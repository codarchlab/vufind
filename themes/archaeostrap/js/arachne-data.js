'use strict';

var xmlhttp = new XMLHttpRequest();
var arachneQueryUrl = 'https://arachne.dainst.org/data/search?q=references.zenonId:';

var container = null;
var heading = 'iDAI.objects/Arachne';

function trimZenonId(zenonId){
  return zenonId.replace("DAI-", "");
}

function startQuery() {
  container = document.getElementById('arachne-data');
  var zenonId = trimZenonId(container.getAttribute('zenon-id'));

  xmlhttp.open("GET", arachneQueryUrl + zenonId, true);
  xmlhttp.send();
}

xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
      var response = JSON.parse(xmlhttp.responseText);
      if (xmlhttp.status == 200 && response['size'] > 0) {
        generateArachneLinks(response['entities']);
      }
      else {
        container.style.visibility = 'hidden';
      }
    }
};

function generateArachneLinks(result) {
  var th = document.createElement('th');
  var thText = document.createTextNode(heading);

  th.appendChild(thText);
  container.appendChild(th);

  var td = document.createElement('td');
  for(var index in result){
    var entity = result[index];
    var div = document.createElement('div');
    var a = document.createElement('a');
    a.href = entity['@id'];
    a.target='_blank';

    var icon = document.createElement('i');
    var textElement = document.createTextNode(entity['title']);
    var small = null;
    if(entity['subtitle']) {
      var subTextElement = document.createTextNode(" | " + entity['subtitle']);
      var small = document.createElement('small');
      small.appendChild(subTextElement);
    }

    icon.className += 'fa fa-university';
    icon.setAttribute('area-hidden', true);

    a.appendChild(icon);
    a.appendChild(textElement);
    div.appendChild(a);
    if(small != null){
      div.append(small);
    }
    td.appendChild(div);
  }
  container.appendChild(td);
}

document.onload = startQuery();
