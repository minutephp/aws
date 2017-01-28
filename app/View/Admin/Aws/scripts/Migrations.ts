/// <reference path="../../../../../../../../public/static/bower_components/minute/_all.d.ts" />

module App {
    export class MigrationListController {
        constructor(public $scope: any, public $minute: any, public $ui: any, public $timeout: ng.ITimeoutService,
                    public gettext: angular.gettext.gettextFunction, public gettextCatalog: angular.gettext.gettextCatalog) {

            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');
            $scope.data = {phinx: {pending: false}};
        }

        run = () => {
            this.$ui.confirm(this.gettext('Have you made a backup of your database? Press OK to run migrations'))
                .then(() => {
                    $('#runner').submit();
                    this.$timeout(() => this.$scope.data.running = true);
                });
        };
    }

    angular.module('migrationListApp', ['MinuteFramework', 'AdminApp', 'gettext'])
        .controller('migrationListController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', MigrationListController]);
}
