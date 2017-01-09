(function () {
    angular.module("app")
        .factory("issuesFactory", issuesFactory);

    issuesFactory.$inject = ["$http", "$q", "$window"];

    function issuesFactory($http, $q, $window) {
        var factory = {};

        factory.loadIssues = function () {
            return $http.get('backend/load_issues.php').then(function (payload) {
                console.log(payload.data);
                return payload.data;
            });
        };

        factory.loadPriorities = function () {
            return $http.get('backend/load_priorities.php').then(function (payload) {
                console.log(payload.data);
                return payload.data;
            });
        };

        factory.updateIssue = function (putData) {
            return $http.post('backend/update_issue.php', putData).success(function (data, status, headers, config) {
                // saving successful
                console.log(data);
            }).error(function (data, status, headers, config) {
                console.log(data);
            });

        };

        factory.deleteUser = function (user) {
            return $http.post('backend/delete_user.php', user).success(function (data, status, headers, config) {
                // saving successful
            }).error(function (data, status, headers, config) {
                console.log(data);
            });

        };

        return factory;

    }
}());