var Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore.enableSassLoader();

Encore
    .setOutputPath('public/assets/')
    .setPublicPath('/assets')
    .setManifestKeyPrefix('bundles/easyadmin')
    .addEntry('js/app', './assets/js/app.js')
    .addEntry('js/recaptcha', './assets/js/recaptcha.js')
    .addEntry('js/article', './assets/js/article.js')
    .addEntry('js/datagrid', './assets/js/datagrid.js')
    .addEntry('js/admin', './assets/js/admin.js')
    .addEntry('js/tag', './assets/js/tag.js')
    .addStyleEntry('css/app', './assets/css/app.scss')
    .addStyleEntry('css/user', './assets/css/user.scss')
    .addStyleEntry('css/experience', './assets/css/experience.scss')
    .addStyleEntry('css/contactme', './assets/css/contactme.scss')
    .addStyleEntry('css/jquery-ui', './node_modules/jquery-ui/themes/base/all.css')
    .addStyleEntry('css/jquery-ui/datepicker', './node_modules/jquery-ui/themes/base/datepicker.css')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSassLoader()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })
    .copyFiles({
        from: './vendor/tinymce',
        to: 'plugin/tinymce/[path][name].[ext]'
    })
    .copyFiles({
        from: './assets/js/localization/tinymce',
        to: 'plugin/tinymce/tinymce/langs/[path][name].[ext]'
    })
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
