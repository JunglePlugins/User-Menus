#!/usr/bin/env node

'use strict';

var fs = require( 'fs' );
var path = require( 'path' );

function read( root, file ) {
    return fs.readFileSync( path.join( root, file ), 'utf8' );
}

function match( contents, pattern, label ) {
    var found = contents.match( pattern );
    if ( ! found ) {
        throw new Error( 'Could not read ' + label + '.' );
    }
    return found[ 1 ];
}

function compareVersions( left, right ) {
    var a = left.split( '.' ).map( Number );
    var b = right.split( '.' ).map( Number );
    var index;
    for ( index = 0; index < 3; index++ ) {
        if ( a[ index ] !== b[ index ] ) {
            return a[ index ] - b[ index ];
        }
    }
    return 0;
}

function validDateHeading( contents, pattern ) {
    var found = contents.match( pattern );
    var month;
    var day;
    var year;
    var leapYear;
    var days;
    if ( ! found ) {
        return false;
    }
    month = Number( found[ 1 ] );
    day = Number( found[ 2 ] );
    year = Number( found[ 3 ] );
    leapYear = 0 === year % 400 || ( 0 === year % 4 && 0 !== year % 100 );
    days = [ 31, leapYear ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];
    return month >= 1 && month <= 12 && day >= 1 && day <= days[ month - 1 ];
}

function validateReleaseVersion( options ) {
    var root = options.projectRoot || process.cwd();
    var version = options.version || '';
    var previousVersion = options.previousVersion || '';
    var plugin;
    var readme;
    var versions;
    var labels;
    var escaped;
    var date;
    var index;

    if ( ! /^\d+\.\d+\.\d+$/.test( version ) ) {
        throw new Error( 'Release version must use stable X.Y.Z format.' );
    }
    if ( previousVersion && compareVersions( version, previousVersion ) <= 0 ) {
        throw new Error( 'Release ' + version + ' must be newer than ' + previousVersion + '.' );
    }

    plugin = read( root, 'user-menus.php' );
    readme = read( root, 'readme.txt' );
    versions = {
        'package.json': JSON.parse( read( root, 'package.json' ) ).version,
        'user-menus.php header': match( plugin, /^\s*\*\s*Version:\s*([^\s]+)\s*$/m, 'plugin version' ),
        'readme.txt stable tag': match( readme, /^Stable tag:\s*([^\s]+)\s*$/m, 'stable tag' )
    };
    labels = Object.keys( versions );
    for ( index = 0; index < labels.length; index++ ) {
        if ( versions[ labels[ index ] ] !== version ) {
            throw new Error( labels[ index ] + '=' + versions[ labels[ index ] ] + '; expected ' + version + '.' );
        }
    }

    escaped = version.replace( /\./g, '\\.' );
    date = '(\\d{2})/(\\d{2})/(\\d{4})';
    if ( ! validDateHeading( readme, new RegExp( '^= v' + escaped + ' - ' + date + ' =$', 'm' ) ) ) {
        throw new Error( 'readme.txt has no valid dated v' + version + ' changelog entry.' );
    }
    return versions;
}

function argument( name ) {
    var index = process.argv.indexOf( name );
    return index < 0 ? '' : process.argv[ index + 1 ] || '';
}

if ( require.main === module ) {
    try {
        validateReleaseVersion( {
            version: argument( '--version' ),
            previousVersion: argument( '--previous-version' )
        } );
        console.log( 'Release version validation passed.' );
    } catch ( error ) {
        console.error( error.message );
        process.exit( 1 );
    }
}

module.exports = {
    compareVersions: compareVersions,
    validateReleaseVersion: validateReleaseVersion
};
