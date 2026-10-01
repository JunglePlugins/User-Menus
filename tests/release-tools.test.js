'use strict';

var assert = require( 'assert' );
var fs = require( 'fs' );
var os = require( 'os' );
var path = require( 'path' );
var validate = require( '../bin/validate-release-version' );
var artifact = require( '../bin/verify-release-artifact' );

var root = fs.mkdtempSync( path.join( os.tmpdir(), 'user-menus-release-' ) );
fs.writeFileSync( path.join( root, 'package.json' ), JSON.stringify( { version: '1.3.3' } ) );
fs.writeFileSync( path.join( root, 'user-menus.php' ), ' * Version: 1.3.3\n' );
fs.writeFileSync( path.join( root, 'readme.txt' ), 'Stable tag: 1.3.3\n\n= v1.3.3 - 09/30/2026 =\n' );

assert.strictEqual( validate.compareVersions( '1.3.3', '1.3.2' ) > 0, true );
assert.strictEqual(
    validate.validateReleaseVersion( {
        projectRoot: root,
        version: '1.3.3',
        previousVersion: '1.3.2'
    } )[ 'package.json' ],
    '1.3.3'
);
assert.deepStrictEqual(
    artifact.inspectEntries(
        [ 'user-menus/', 'user-menus/user-menus.php', 'user-menus/readme.txt', 'user-menus/freemius/start.php' ],
        [ 'd', '-', '-', '-' ]
    ),
    []
);
assert.strictEqual(
    artifact.inspectEntries(
        [ 'user-menus/', 'user-menus/user-menus.php', 'user-menus/readme.txt', 'user-menus/freemius/start.php', 'user-menus/.github/workflows/release.yml' ],
        [ 'd', '-', '-', '-', '-' ]
    ).some( function( failure ) { return -1 !== failure.indexOf( 'forbidden' ); } ),
    true
);

console.log( 'Release tool tests passed.' );
