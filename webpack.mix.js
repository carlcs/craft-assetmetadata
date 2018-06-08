const mix = require('laravel-mix');

const paths = {
    src: 'src/web/assets/src',
    dist: 'src/web/assets/dist',
};

mix.js(paths.src+'/assetmetadata.js', paths.dist)
    .sass(paths.src+'/assetmetadata.scss', paths.dist);
