(function () {
    angular.module("app")
        .controller("IssuesController", IssuesController);

    IssuesController.$inject = ["$scope", "issuesFactory"];

    function IssuesController($scope, issuesFactory) {
        $scope.baseUrl = "https://pantheon.atlassian.net/browse/";
        $scope.data = {};
        $scope.issues = [];
        $scope.priorities = [];

        function loadData() {
            issuesFactory.loadIssues()
                .then(function (response) {
                    $scope.data = response;
                    $scope.issues = response.issues;
                });

            issuesFactory.loadPriorities()
                .then(function (response) {
                    $scope.priorities = response;
                });
        }

        loadData();

        $scope.changePriority = function (issue, priority) {
            var updateObject = {
                "update": {
                    "priority": [{
                        "set": {
                            "id": priority.id
                        }
                    }]
                }
            };
            var putData = {
                id: issue.id,
                data: updateObject
            };
            issuesFactory.updateIssue(putData)
                .then(function (response) {
                    console.log(response);
                });
            angular.extend(issue.fields.priority, priority);
        }
    }
}());





