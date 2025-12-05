const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		'components': path.resolve( __dirname, 'src/components/index.ts' ),
		'data': path.resolve( __dirname, 'src/data/index.ts' ),
		'menu-editor': path.resolve( __dirname, 'src/menu-editor/index.tsx' ),
	},
	output: {
		path: path.resolve( __dirname, 'dist' ),
		filename: '[name].js',
		library: [ 'userMenus', '[name]' ],
		libraryTarget: 'window',
	},
	resolve: {
		...defaultConfig.resolve,
		extensions: [ '.ts', '.tsx', '.js', '.jsx', '.json' ],
		alias: {
			...( defaultConfig.resolve?.alias || {} ),
			'@': path.resolve( __dirname, 'src' ),
		},
	},
};
