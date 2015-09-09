angular.module('zenonThs', [])

  .controller('ThsController', ['$scope', '$http', '$element', function($scope, $http, $element) {

    $scope.columns = [];
    $scope.active = [];
    $scope.offset = 0;
    $scope.colWidth = $element[0].offsetWidth / 4;
    $scope.loading = 1;

    $http.get('/Thesaurus/Children').success(function(result) {
      $scope.columns[0] = result.data;
      $scope.loading--;
    });

    $scope.loadColumn = function(colNo, parentEntry) {
      $scope.columns = $scope.columns.slice(0, colNo);
      $scope.active = $scope.active.slice(0, colNo-1);
      $scope.active[colNo-1] = parentEntry;
      if (parentEntry.children_str_mv) {
        var column = [];
        parentEntry.children_str_mv.forEach(function(child, i) {
          column.push({
            heading: child, id:parentEntry.id + '-' + i, leaf: true,
            inline: true, qualifier_str: parentEntry.qualifier_str
          });
        });
        $scope.columns[colNo] = column;
        calcOffset(colNo);
      } else {
        $scope.loading++;
        $http.get('/Thesaurus/Children?id=' + parentEntry.id).success(function(result) {
          if (result.data.length < 1) {
            parentEntry.leaf = true;
            calcOffset(colNo-1);
          } else {
            $scope.columns[colNo] = result.data;
            calcOffset(colNo);
          }
          $scope.loading--;
        });
      }
    };

    $scope.search = function(entry) {
      if(entry.inline) {
        window.location = "/Search/Results?lookfor=\""+entry.qualifier_str+"\" \""+entry.heading+"\"";
      } else {
        window.location = "/Search/Results?lookfor=\""+entry.qualifier_str+"\"&type=Thesaurus";
      }
    };

    function calcOffset(colNo) {
      if (colNo > 3) {
        $scope.offset = (colNo - 3) * $scope.colWidth;
      } else {
        $scope.offset = 0;
      }
    }

  }]);
