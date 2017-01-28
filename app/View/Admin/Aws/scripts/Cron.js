/// <reference path="E:/var/Dropbox/projects/minutephp/public/static/bower_components/minute/_all.d.ts" />
var Admin;
(function (Admin) {
    var CronController = (function () {
        function CronController($scope, $minute, $ui, $timeout, gettext, gettextCatalog) {
            this.$scope = $scope;
            this.$minute = $minute;
            this.$ui = $ui;
            this.$timeout = $timeout;
            this.gettext = gettext;
            this.gettextCatalog = gettextCatalog;
            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');
        }
        return CronController;
    }());
    Admin.CronController = CronController;
    angular.module('CronApp', ['MinuteFramework', 'gettext'])
        .controller('CronController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', CronController]);
})(Admin || (Admin = {}));
