const mix = require('laravel-mix')
const tailwindcss = require('tailwindcss')

mix.js('resources/assets/js/app.js', 'public/dist/app.js').vue({ version: 2 })
	.sass('resources/assets/scss/app.scss', 'public/dist/app.css')
	.options({
		processCssUrls: false,
		postCss: [tailwindcss('tailwind.js')],
	});

if (mix.inProduction()) {
	mix.version()
} else {
	mix.sourceMaps()
}
