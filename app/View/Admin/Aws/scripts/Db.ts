/// <reference path="../../../../../../../../public/static/bower_components/minute/_all.d.ts" />

module Admin {
    export class DatabaseConfigController {
        constructor(public $scope: any, public $minute: any, public $ui: any, public $timeout: ng.ITimeoutService,
                    public gettext: angular.gettext.gettextFunction, public gettextCatalog: angular.gettext.gettextCatalog) {

            gettextCatalog.setCurrentLanguage($scope.session.lang || 'en');
            $scope.data = {loading: false, scroll: true, tweak: true};
            $scope.config = $scope.configs[0] || $scope.configs.create().attr('type', 'database').attr('data_json', {});
            $scope.settings = $scope.config.attr('data_json');
        }

        mirror = (tweak) => {
            this.$ui.confirm(this.gettext("This will erase your current RDS instance and replace it contents of your local database.<br><br>Are you sure?"))
                .then(() => {
                    this.$scope.data.loading = true;
                    this.$scope.data.scroll = true;

                    $('#databaseForm').submit();

                    this.$timeout(this.scroll);
                });
        };

        scroll = () => {
            var $contents = $('#frameProgress').contents();

            if (this.$scope.data.scroll) {
                $contents.scrollTop($contents.height());
            }

            if (this.$scope.data.loading) {
                this.$timeout(this.scroll, 100);
            }
        }
    }

    angular.module('databaseConfigApp', ['MinuteFramework', 'AdminApp', 'gettext'])
        .controller('databaseConfigController', ['$scope', '$minute', '$ui', '$timeout', 'gettext', 'gettextCatalog', DatabaseConfigController]);
}
