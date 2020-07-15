angular.module('zenonThs', [])

  .controller('ThsController', ['$scope', '$http', '$element', function($scope, $http, $element) {

    $scope.columns = [];
    $scope.active = [];
    $scope.offset = 0;
    $scope.colWidth = angular.element($element[0]).children()[1].offsetWidth / 4;
    $scope.loading = 1;

    $http.get('/Thesaurus/Children').success(function(result) {
      $scope.columns[0] = result.data;
      $scope.loading--;
    });

    $scope.loadColumn = function(colNo, parentEntry) {
      $scope.columns = $scope.columns.slice(0, colNo);
      $scope.active = $scope.active.slice(0, colNo-1);
      $scope.active[colNo-1] = parentEntry;
      if (parentEntry.ths_children_str_mv) {
        var column = [];
        parentEntry.ths_children_str_mv.forEach(function(child, i) {
          column.push({
            heading: child, id:parentEntry.ths_id_str+ '-' + i, leaf: true,
            inline: true, ths_qualifier_str: parentEntry.ths_qualifier_str
          });
        });
        $scope.columns[colNo] = column;
        calcOffset(colNo);
      } else {
        $scope.loading++;
        $http.get('/Thesaurus/Children?id=' + parentEntry.ths_id_str).success(function(result) {
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
        window.location = "/Search/Results?lookfor=\""+entry.ths_qualifier_str+"\" \""+entry.ths_heading+"\"";
      } else {
        window.location = "/Search/Results?lookfor=\""+entry.ths_qualifier_str+"\"&type=Thesaurus";
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
