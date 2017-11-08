var gulp = require('gulp'),
	argv = require('yargs').argv,
	theme = argv.theme || 'all',
	sourceMapsDir = './',
	plugins = require("gulp-load-plugins")({
		pattern: ['gulp-*', 'gulp.*', 'main-bower-files', 'jshint-stylish', 'del'],
		scope: ['devDependencies'],
		replaceString: /^gulp(-|\.)/,
		camelize: true,
		lazy: true
	}),
	paths = {
		'dev': {
			'scripts': 'develop/',
			'vendor': 'bower_components/'
		},
		'prod': {
			'scripts': 'styles/' + theme + '/theme/assets/',
			'vendor': 'styles/' + theme + '/theme/vendor/'
		}
	},
	supportedBrowsers = ["last 1 version", "> 1%", "ie 8"]

// Bower
gulp.task('bower', function() {
	return plugins.bower()
		.pipe(gulp.dest(paths.dev.vendor));
});

// Scripts
gulp.task('scripts', function() {
	var jsFilter = plugins.filter(['**/*.js', '!**/*.min.js'], {restore: true});
	var cssFilter = plugins.filter(['**/*.css', '!**/*.min.css']);

	return gulp.src(paths.dev.scripts + '**')
		.pipe(plugins.changed(paths.prod.scripts))
		.pipe(jsFilter)
			.pipe(plugins.sourcemaps.init())
				.pipe(plugins.eslint())
				.pipe(plugins.eslint.format())
				.pipe(plugins.rename({ suffix: '.min' }))
				.pipe(plugins.uglify())
			.pipe(plugins.sourcemaps.write(sourceMapsDir))
			.pipe(gulp.dest(paths.prod.scripts))
			.pipe(jsFilter.restore)
		.pipe(cssFilter)
			.pipe(plugins.sourcemaps.init())
				.pipe(plugins.csscomb())
				.pipe(gulp.dest(paths.dev.scripts))
				.pipe(plugins.csslint({
					'ids': false,
					'adjoining-classes': false,
					'box-sizing': false,
					'order-alphabetical': false
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
		.pipe(jsFilter)
			.pipe(plugins.rename({ suffix: '.min' }))
			.pipe(plugins.uglify())
			.pipe(gulp.dest(paths.prod.vendor))
			.pipe(jsFilter.restore)
		.pipe(cssFilter)
			.pipe(plugins.rename({ suffix: '.min' }))
			.pipe(plugins.minifyCss())
			.pipe(gulp.dest(paths.prod.vendor))
			.pipe(cssFilter.restore)
		.pipe(gulp.dest(paths.prod.vendor));
});

// Clean up
gulp.task('clean', function() {
	return plugins.del([
		paths.prod.scripts + '**',
		paths.prod.vendor + '**'
	]);
});

gulp.task('rebuild_vendors', ['bower'], function() {
	gulp.start('vendor');
});

gulp.task('watch', function() {
	// Watch script files
	gulp.watch([paths.dev.scripts + '**/*.css', paths.dev.scripts + '**/*.js'], ['scripts']);

	// Watch Vendor files
	gulp.watch(paths.dev.vendor + '**', ['vendor']);

	// Watch bower.json
	gulp.watch('./bower.json', ['rebuild_vendors']);
});

gulp.task('build', ['clean'], function() {
	gulp.start('scripts', 'vendor');
});