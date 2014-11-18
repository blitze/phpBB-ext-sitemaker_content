var gulp = require('gulp'),
	plugins = require("gulp-load-plugins")({
		pattern: ['gulp-*', 'gulp.*', 'main-bower-files', 'jshint-stylish', 'del'],
		replaceString: /\bgulp[\-.]/,
		camelize: true
	}),
	paths = {
		'dev': {
			'js': './develop/js/*.js',
			'vendor': './develop/vendor/',
			'css': './develop/css/*.css',
			'theme': './develop/theme/*.css'
		},
		'prod': {
			'js': './assets/js/',
			'css': './assets/css/',
			'vendor': './assets/vendor/',
			'theme': './styles/prosilver/theme/'
		}
	};

var filterByExtension = function(extension) {  
	return plugins.filter(function(file) {
		return file.path.match(new RegExp('.' + extension + '$'));
	});
};

// Bower
gulp.task('bower', function() {
	return plugins.bower()
		.pipe(gulp.dest(paths.dev.vendor))
});

// CSS
gulp.task('css', function() {
	return gulp.src(paths.dev.css)
		.pipe(plugins.csscomb())
		.pipe(gulp.dest('./develop/css/'))
		.pipe(plugins.csslint())
		.pipe(plugins.csslint.reporter())
		.pipe(plugins.rename({ suffix: '.min' }))
		.pipe(plugins.minifyCss())
		.pipe(gulp.dest(paths.prod.css))
		.pipe(plugins.notify({ message: 'CSS task complete' }));
});

// Theme-specific CSS
gulp.task('theme', function() {
	return gulp.src(paths.dev.theme)
		.pipe(plugins.csscomb())
		.pipe(gulp.dest('./develop/theme/'))
		.pipe(plugins.csslint())
		.pipe(plugins.csslint.reporter())
		.pipe(plugins.rename({ suffix: '.min' }))
		.pipe(plugins.minifyCss())
		.pipe(gulp.dest(paths.prod.theme))
		.pipe(plugins.notify({ message: 'Theme task complete' }));
});

// js
gulp.task('js', function() {
	return gulp.src(paths.dev.js)
		.pipe(plugins.jscs())
		.pipe(plugins.jshint())
		.pipe(plugins.jshint.reporter(plugins.jshintStylish))
		.pipe(plugins.jshint.reporter('fail'))
		.pipe(plugins.rename({ suffix: '.min' }))
		.pipe(plugins.uglify())
		.pipe(gulp.dest(paths.prod.js))
		.pipe(plugins.notify({ message: 'JS task complete' }));
});

// Vendor
gulp.task('vendor', function() {
	var mainFiles = plugins.mainBowerFiles();

	if (!mainFiles.length) {
		// No main files found. Skipping....
		return;
	}

	var jsFilter = filterByExtension('js');

	return gulp.src(mainFiles, {base: paths.dev.vendor })
		.pipe(jsFilter)
		.pipe(plugins.rename({ suffix: '.min' }))
		.pipe(plugins.uglify())
		.pipe(gulp.dest(paths.prod.vendor))
		.pipe(jsFilter.restore())
		.pipe(filterByExtension('css'))
		.pipe(plugins.rename({ suffix: '.min' }))
		.pipe(plugins.minifyCss())
		.pipe(gulp.dest(paths.prod.vendor))
		.pipe(plugins.notify({ message: 'Vendor task complete' }));
});

// Clean up
gulp.task('clean', function(cb) {
	plugins.del([
		paths.prod.js,
		paths.prod.css,
		paths.prod.vendor,
		paths.prod.theme
	], cb);
});

gulp.task('watch', function() {

  // Watch .css files
  gulp.watch(paths.dev.css + '*.css', ['css']);

  // Watch .js files
  gulp.watch(paths.dev.js + '*.js', ['js']);

  // Watch bower.json
  gulp.watch('./bower.json', ['bower']);

});

gulp.task('build', ['clean'], function() {
	gulp.start('css', 'theme', 'js', 'vendor');
});