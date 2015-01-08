var gulp = require('gulp'),
	plugins = require("gulp-load-plugins")({
		pattern: ['gulp-*', 'gulp.*', 'main-bower-files', 'jshint-stylish', 'del'],
		replaceString: /\bgulp[\-.]/,
		camelize: true
	}),
	argv = require('yargs').argv,
	production = !!(argv.production), // true if --production flag is used
	paths = {
		'dev': {
			'scripts': 'develop/scripts/',
			'theme': 'develop/theme/',
			'vendor': 'bower_components/'
		},
		'prod': {
			'scripts': 'assets/scripts/',
			'theme': 'styles/prosilver/theme/',
			'vendor': 'assets/vendor/'
		}
	};

// Bower
gulp.task('bower', function() {
	return plugins.bower()
		.pipe(gulp.dest(paths.dev.vendor))
});

// Scripts
gulp.task('scripts', function() {
	var jsFilter = plugins.filter(['**/*.js', '!**/*.min.js']);
	var cssFilter = plugins.filter(['**/*.css', '!**/*.min.css']);

	return gulp.src(paths.dev.scripts + '**')
		.pipe(jsFilter)
		.pipe(plugins.jscs())
		.pipe(plugins.jshint())
		.pipe(plugins.jshint.reporter(plugins.jshintStylish))
		.pipe(plugins.rename({ suffix: '.min' }))
		.pipe(plugins.if(production, plugins.uglify()))
		.pipe(gulp.dest(paths.prod.scripts))
		.pipe(jsFilter.restore())
		.pipe(cssFilter)
		.pipe(plugins.csscomb())
		.pipe(gulp.dest(paths.dev.scripts))
		.pipe(plugins.csslint())
		.pipe(plugins.csslint.reporter())
		.pipe(plugins.autoprefixer())
		.pipe(plugins.rename({ suffix: '.min' }))
		.pipe(plugins.minifyCss())
		.pipe(gulp.dest(paths.prod.scripts))
		.pipe(plugins.notify({ message: 'Scripts task complete' }));
});

// Theme-specific CSS
gulp.task('theme', function() {
	return gulp.src(paths.dev.theme + '*.css')
		.pipe(plugins.csscomb())
		.pipe(gulp.dest(paths.dev.theme))
		.pipe(plugins.csslint())
		.pipe(plugins.csslint.reporter())
		.pipe(plugins.autoprefixer())
		.pipe(plugins.rename({ suffix: '.min' }))
		.pipe(plugins.minifyCss())
		.pipe(gulp.dest(paths.prod.theme))
		.pipe(plugins.notify({ message: 'Theme task complete' }));
});

// Vendor
gulp.task('vendor', function() {
	var mainFiles = plugins.mainBowerFiles();

	if (!mainFiles.length) {
		return;
	}

	var jsFilter = plugins.filter(['**/*.js', '!**/*.min.js']);
	var cssFilter = plugins.filter(['**/*.css', '!**/*.min.css']);

	return gulp.src(mainFiles, {base: paths.dev.vendor })
		.pipe(jsFilter)
		.pipe(plugins.rename({ suffix: '.min' }))
		.pipe(plugins.if(production, plugins.uglify()))
		.pipe(gulp.dest(paths.prod.vendor))
		.pipe(jsFilter.restore())
		.pipe(cssFilter)
		.pipe(plugins.rename({ suffix: '.min' }))
		.pipe(plugins.minifyCss())
		.pipe(gulp.dest(paths.prod.vendor))
		.pipe(cssFilter.restore())
		.pipe(gulp.dest(paths.prod.vendor))
		.pipe(plugins.notify({ message: 'Vendor task complete' }));
});

// Clean up
gulp.task('clean', function(cb) {
	plugins.del([
		'assets/',
		paths.prod.theme + '*.css',
		paths.prod.vendor
	], cb);
});

gulp.task('watch', function() {
	// Watch script files
	gulp.watch([paths.dev.scripts + '**/*.css', paths.dev.scripts + '**/*.js'], ['scripts']);

	// Watch theme files
	gulp.watch(paths.dev.theme + '*.css', ['theme']);

	// Watch Vendor files
	gulp.watch(paths.dev.vendor + '**', ['vendor']);

	// Watch bower.json
	gulp.watch('./bower.json', ['bower']);
});

gulp.task('build', ['clean'], function() {
	gulp.start('theme', 'scripts', 'vendor');
});