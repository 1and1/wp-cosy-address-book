module.exports = function ( grunt ) {
	grunt.initConfig( {
		po2mo: {
			files: {
				src: 'cosy-address-book/languages/*.po',
				expand: true
			}
		},
		less: {
			development: {
				options: {
					paths: ['cosy-address-book/css']
				},
				files: {
					'cosy-address-book/css/address-book.css': 'cosy-address-book/css/address-book.less',
				}
			}
		},
		copy: {
			main: {
				files: [
					{
						src: 'cosy-address-book/languages/cosy-address-book-de_DE.po',
						dest: 'cosy-address-book/languages/cosy-address-book-de_CH_informal.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-de_DE_formal.po',
						dest: 'cosy-address-book/languages/cosy-address-book-de_CH.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-en_GB.po',
						dest: 'cosy-address-book/languages/cosy-address-book-en_AU.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-en_GB.po',
						dest: 'cosy-address-book/languages/cosy-address-book-en_NZ.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-es_MX.po',
						dest: 'cosy-address-book/languages/cosy-address-book-es_GT.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-es_MX.po',
						dest: 'cosy-address-book/languages/cosy-address-book-es_AR.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-es_MX.po',
						dest: 'cosy-address-book/languages/cosy-address-book-es_VE.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-es_MX.po',
						dest: 'cosy-address-book/languages/cosy-address-book-es_CO.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-es_MX.po',
						dest: 'cosy-address-book/languages/cosy-address-book-es_CR.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-es_MX.po',
						dest: 'cosy-address-book/languages/cosy-address-book-es_CL.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-es_MX.po',
						dest: 'cosy-address-book/languages/cosy-address-book-es_PE.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-fr_FR.po',
						dest: 'cosy-address-book/languages/cosy-address-book-fr_BE.po'
					},
					{
						src: 'cosy-address-book/languages/cosy-address-book-fr_FR.po',
						dest: 'cosy-address-book/languages/cosy-address-book-fr_CA.po'
					}
				]
			}
		},
		makepot: {
			target: {
				options: {
					mainFile: 'cosy-address-book.php',
					domainPath: 'cosy-address-book/languages',
					exclude: ['bin/.*', '.git/.*', 'tests/.*', 'vendor/.*', 'node_modules/.*'],
					type: 'wp-plugin',
					potFilename: 'cosy-address-book.pot'
				}
			}
		},
		watch: {
			i18n: {
				files: ['**/*.po'],
				tasks: ['i18n']
			},
			less: {
				files: "cosy-address-book/css/*.less",
				tasks: ["less"]
			}
		}
	} );

	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-po2mo' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );
	grunt.loadNpmTasks( 'grunt-contrib-less' );

	grunt.registerTask( 'build-i18n', [
		'makepot',
		'copy',
		'po2mo',
		'less'
	] );

	grunt.registerTask( 'default', [
		'build-i18n'
	] );
};
