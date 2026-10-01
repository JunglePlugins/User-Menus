#!/usr/bin/env node

'use strict';

var fs = require( 'fs' );
var execFileSync = require( 'child_process' ).execFileSync;

var root = 'user-menus';
var required = [ 'user-menus.php', 'readme.txt', 'freemius/start.php' ];
var forbidden = [
    '.git/', '.github/', '.svn/', '.wordpress-org/', 'assets/js/src/',
    'assets/sass/', 'bin/', 'build/', 'node_modules/', 'release/', 'tests/', 'vendor/'
];

function relative( entry ) {
    var normalized = entry.replace( /\\/g, '/' ).replace( /\/$/, '' );
    return 0 === normalized.indexOf( root + '/' ) ? normalized.slice( root.length + 1 ) : normalized;
}

function inspectEntries( entries, entryTypes ) {
    var failures = [];
    var seen = {};
    var paths = {};
    var index;
    var entry;
    var normalized;
    var parts;
    var file;

    if ( entryTypes.length !== entries.length ) {
        failures.push( 'could not verify every archive entry type' );
    }
    for ( index = 0; index < entries.length; index++ ) {
        entry = entries[ index ];
        normalized = entry.replace( /\\/g, '/' );
        parts = normalized.replace( /\/$/, '' ).split( '/' );
        if ( entry !== normalized || '/' === normalized.charAt( 0 ) || -1 !== parts.indexOf( '..' ) || -1 !== parts.indexOf( '.' ) ) {
            failures.push( 'unsafe path: ' + entry );
        }
        if ( parts[ 0 ] !== root ) {
            failures.push( 'unexpected root: ' + entry );
        }
        if ( seen[ normalized ] ) {
            failures.push( 'duplicate path: ' + entry );
        }
        seen[ normalized ] = true;
        paths[ relative( entry ) ] = true;
        if ( entryTypes[ index ] && '-' !== entryTypes[ index ] && 'd' !== entryTypes[ index ] ) {
            failures.push( 'forbidden archive entry type: ' + entry );
        }
    }
    for ( index = 0; index < required.length; index++ ) {
        if ( ! paths[ required[ index ] ] ) {
            failures.push( 'missing: ' + required[ index ] );
        }
    }
    for ( file in paths ) {
        if ( forbidden.some( function( prefix ) { return 0 === file.indexOf( prefix ); } ) || /^[^/]+\.md$/i.test( file ) ) {
            failures.push( 'forbidden: ' + file );
        }
    }
    return failures;
}

function verify( zipPath, version ) {
    var entries;
    var listing;
    var entryTypes;
    var failures;
    var header;
    var found;
    if ( ! fs.existsSync( zipPath ) ) {
        throw new Error( 'Artifact not found: ' + zipPath );
    }
    entries = execFileSync( 'unzip', [ '-Z1', zipPath ], { encoding: 'utf8' } ).trim().split( '\n' ).filter( Boolean );
    listing = execFileSync( 'unzip', [ '-Z', '-l', zipPath ], { encoding: 'utf8' } );
    entryTypes = listing.split( '\n' )
        .filter( function( line ) { return /^[bcdlps-].{9}\s/.test( line ); } )
        .map( function( line ) { return line.charAt( 0 ); } );
    failures = inspectEntries( entries, entryTypes );
    if ( version ) {
        header = execFileSync( 'unzip', [ '-p', zipPath, root + '/user-menus.php' ], { encoding: 'utf8' } );
        found = header.match( /^\s*\*\s*Version:\s*([^\s]+)\s*$/m );
        if ( ! found || found[ 1 ] !== version ) {
            failures.push( 'artifact version does not match ' + version );
        }
    }
    if ( failures.length ) {
        throw new Error( 'Artifact verification failed:\n- ' + failures.join( '\n- ' ) );
    }
    return entries;
}

if ( require.main === module ) {
    try {
        console.log( 'Release artifact verification passed (' + verify( process.argv[ 2 ], process.env.VERSION || '' ).length + ' entries).' );
    } catch ( error ) {
        console.error( error.message );
        process.exit( 1 );
    }
}

module.exports = { inspectEntries: inspectEntries, relative: relative, verify: verify };
