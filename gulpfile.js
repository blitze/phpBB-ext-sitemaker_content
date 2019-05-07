var gulp = require('gulp'),
	argv = require('yargs').argv,
	plugins = require("gulp-load-plugins")({
		pattern: ['gulp-*', 'gulp.*', 'main-bower-files', 'jshint-stylish', 'del'],
		scope: ['devDependencies'],
		replaceString: /^gulp(-|\.)/,
		camelize: true,
		lazy: true
	}),
	supportedBrowsers = ["last 1 version", "> 1%", "ie 8"],
	sassOptions = {
		errLogToConsole: true,
		outputStyle: 'expanded'
	},
	sourceMapsDir = './',
	theme = argv.theme || 'all',
	paths = {
		'dev': {
			'scripts': 'develop/',
			'vendor': 'bower_components/'
		},
		'prod': {
			'scripts': 'styles/' + theme + '/theme/assets/',
			'vendor': 'styles/' + theme + '/theme/vendor/'
		}
	};

// Bower
gulp.task('bower', function() {
	return plugins.bower()
		.pipe(gulp.dest(paths.dev.vendor));
});

// JS
gulp.task('js', function() {
	return gulp.src(paths.dev.scripts + '**/*.js')
		.pipe(plugins.changed(paths.prod.scripts))
		.pipe(plugins.sourcemaps.init())
			.pipe(plugins.jscs())
			.pipe(plugins.jshint())
			.pipe(plugins.jshint.reporter(plugins.jshintStylish))
			.pipe(plugins.rename({ suffix: '.min' }))
			.pipe(plugins.uglify())
		.pipe(plugins.sourcemaps.write(sourceMapsDir))
		.pipe(gulp.dest(paths.prod.scripts));
});

// SASS
gulp.task('sass', function() {
	return gulp.src(paths.dev.scripts + '**/*.scss')
		.pipe(plugins.changed(paths.prod.scripts))
		.pipe(plugins.sourcemaps.init())
			.pipe(plugins.sass(sassOptions).on('error', plugins.sass.logError))
			.pipe(plugins.csscomb())
			.pipe(plugins.csslint({
				'adjoining-classes': false,
				'box-sizing': false,
				'order-alphabetical': false,
				'regex-selectors': false,
				'unqualified-attributes': false
			}))
			.pipe(plugins.csslint.formatter())
			.pipe(plugins.autoprefixer(supportedBrowsers))
			.pipe(plugins.rename({ suffix: '.min' }))
			.pipe(plugins.cleanCss())
		.pipe(plugins.sourcemaps.write(sourceMapsDir))
		.pipe(gulp.dest(paths.prod.scripts));
});

// Vendor
gulp.task('vendor', function() {
	var mainFiles = plugins.mainBowerFiles();

	if (!mainFiles.length) {
		return;
	}

	var jsFilter = plugins.filter(['**/*.js', '!**/*.min.js'], {restore: true});
	var cssFilter = plugins.filter(['**/*.css', '!**/*.min.css'], {restore: true});

	return gulp.src(mainFiles, {base: paths.dev.vendor })
		.pipe(plugins.changed(paths.prod.vendor))
		.pipe(jsFilter)
			.pipe(plugins.rename({ suffix: '.min' }))
			.pipe(plugins.uglify())
			.pipe(gulp.dest(paths.prod.vendor))
			.pipe(jsFilter.restore)
		.pipe(cssFilter)
			.pipe(plugins.rename({ suffix: '.min' }))
			.pipe(plugins.cleanCss())
			.pipe(plugins.replace('url(../../../ion.rangeSlider/', 'url(../'))
			.pipe(gulp.dest(paths.prod.vendor))
			.pipe(cssFilter.restore)
			.pipe(gulp.dest(paths.prod.vendor));
});

// Clean up
gulp.task('clean', function(done) {
	plugins.del.sync([
		paths.prod.scripts + '**',
		paths.prod.vendor + '**'
	]);
	done();
});

gulp.task('rebuild_vendors', gulp.series('bower', gulp.parallel('vendor')));

gulp.task('watch', function() {
	// Watch js files
	gulp.watch(paths.dev.scripts + '**/*.js', gulp.parallel('js'));

	// Watch sass files
	gulp.watch(paths.dev.scripts + '**/*.scss', gulp.parallel('sass'));

	// Watch bower.json
	gulp.watch('./bower.json', gulp.parallel('rebuild_vendors'));
});

gulp.task('build', gulp.series('clean', gulp.parallel('js', 'sass', 'vendor')));