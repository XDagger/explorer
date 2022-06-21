const mix = require('laravel-mix')
const tailwindcss = require('tailwindcss')
const postcssimport = require('postcss-import')
const postcssnext = require('postcss-cssnext')

require('laravel-mix-purgecss')

mix.js('resources/assets/js/app.js', 'public/dist/app.js')
    .sass('resources/assets/scss/app.scss', 'public/dist/app.css')
    .options({
        processCssUrls: false,
        postCss: [
            postcssimport(),
            tailwindcss('tailwind.js'),
            postcssnext({
                features: {
                    autoprefixer: false
                }
            }),
        ],
    })
    .purgeCss({whitelist: ['h1', 'h2', 'h3', 'p', 'ul', 'code', 'pre']})

if (mix.inProduction()) {
    mix.version()
} else {
    mix.sourceMaps()
}
