/// <reference path="E:/var/Dropbox/projects/minutephp/public/static/bower_components/minute/_all.d.ts" />

module Admin {
    export class CronController {
        constructor(public $scope:any, public $minute:any, public $ui:any, public $timeout:ng.ITimeoutService,
                    public gettext:angular.gettext.gettextFunction, public gettextCatalog:angular.gettext.gettextCatalog) {

            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');
        }
    }

    angular.module('CronApp', ['MinuteFramework', 'gettext'])
        .controller('CronController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', CronController]);
}
