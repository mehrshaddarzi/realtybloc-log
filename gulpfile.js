var gulp = require('gulp'),
    cleanCSS = require('gulp-clean-css'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    insert = require('gulp-insert'),
    babel = require('gulp-babel'),
    uglify = require('gulp-uglify'),
    replace = require('gulp-replace'),
    sass = require('gulp-sass'),
    pipeline = require('readable-stream').pipeline;

// Source Of folder
var source = './src/';
var dist = './dist/';

// Gulp Sass Compiler
sass.compiler = require('node-sass');
gulp.task('sass', function () {
    return gulp.src([
        source + 'sass/admin.scss',
        source + 'sass/rtl.scss'
    ])
        .pipe(sass({outputStyle: 'compressed'}))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest([dist + 'css/']));
});

//Gulp Script Concat
gulp.task('script', function () {
    return gulp.src([
        source + 'javascript/plugin/*.js',
        source + 'javascript/config.js',
        source + 'javascript/ajax.js',
        source + 'javascript/placeholder.js',
        source + 'javascript/helper.js',
        source + 'javascript/run.js',
    ])
        .pipe(concat('admin.min.js'))
        .pipe(insert.prepend('jQuery(document).ready(function ($) {'))
        .pipe(insert.append('});'))
        .pipe(gulp.dest(dist + 'js/'))
        .pipe(babel({presets: ['@babel/env']}))
        .pipe(replace("\\n", ''))
        .pipe(replace("\\t", ''))
        .pipe(replace("  ", ''))
        .pipe(uglify())
        .pipe(gulp.dest(dist + 'js/'));
});

// Gulp Script Minify
gulp.task('js', function () {
    return gulp.src([dist + 'js/*.js', '!./assets/js/*.min.js'])
        .pipe(babel({presets: ['@babel/env']}))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }));
});

// Gulp Css Minify
gulp.task('css', function () {
    return gulp.src([dist + 'css/*.css', '!./assets/css/*.min.css'])
        .pipe(cleanCSS({
            keepSpecialComments: 1,
            level: 2
        }))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(function (file) {
            return file.base;
        }));
});

// Gulp Watch
gulp.task('watch', function () {
    gulp.watch(source + 'javascript/**/*.js', gulp.series('script'));
    gulp.watch(source + 'sass/**/*.scss', gulp.series('sass'));
});

// global Task
gulp.task('default', gulp.parallel('sass', 'script'));