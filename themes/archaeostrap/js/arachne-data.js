'use strict';

var xmlhttp = new XMLHttpRequest();
var arachneQueryUrl = 'https://arachne.dainst.org/data/search?q=references.zenonId:';
var zenonId = null;

var container = null;
var heading = 'iDAI.objects/Arachne';

var resultBuckets = [];
var resultBucketSize = 5;
var resultBucketIndex = 0;

var entityCount = 0;

window.onload = startQuery;

function startQuery() {
  container = document.getElementById('arachne-data');
  zenonId = container.getAttribute('control-number');

  xmlhttp.open("GET", arachneQueryUrl + zenonId, true);
  xmlhttp.send();
}

xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
      var response = JSON.parse(xmlhttp.responseText);
      if (xmlhttp.status == 200 && response['size'] > 0) {
        entityCount = response['size'];
        createBuckets(response['entities']);
        showCurrentBucket();
      }
      else {
        container.parentNode.removeChild(container);
      }
    }
};

function createBuckets(entities) {
  var bucket = [];
  for(var index in entities) {
    if(index != 0 && index % resultBucketSize == 0) {
      resultBuckets.push(bucket);
      bucket = [];
    }
    bucket.push(entities[index]);
  }

  if(bucket.length != 0){
    resultBuckets.push(bucket);
  }
}

function showCurrentBucket() {
  while (container.firstChild) {
      container.removeChild(container.firstChild);
  }

  var th = document.createElement('th');
  var thText = document.createTextNode(heading);

  th.appendChild(thText);
  container.appendChild(th);

  var td = document.createElement('td');

  if(resultBuckets.length > 1) {
    td.appendChild(addPaginationButtons());
  }

  for(var index in resultBuckets[resultBucketIndex]){
    td.appendChild(addArachneEntityLink(resultBuckets[resultBucketIndex][index]));
  }

  container.appendChild(td);
}

function selectBucket(newIndex) {

  if(newIndex < 0) return;
  if(newIndex > resultBuckets.length - 1) return;
  // If there are more results overall for this record than entities yet retrieved (default: 50 per search),
  // apply an offset and load the next batch of entities in the background.
  if(entityCount > resultBuckets.length * resultBucketSize && newIndex === (resultBuckets.length - 2)) {
    xmlhttp.open("GET", arachneQueryUrl + zenonId + "&offset=" + resultBuckets.length * resultBucketSize, true);
    xmlhttp.send();
  }

  resultBucketIndex = newIndex;
  showCurrentBucket()
}

function addPaginationButtons() {
  var nav = document.createElement('nav');
  nav.className += 'navbar-right';
  nav.style = 'margin-right:1px';
  var ul = document.createElement('ul');
  ul.className += 'pager arachne-nav';

  // Add arrow left
  var previous = document.createElement('li');
  var previousLink = document.createElement('a');
  var previousIcon = document.createElement('i');
  previousIcon.className += "fa fa-arrow-left";
  previousIcon.setAttribute('area-hidden', true);
  previousLink.onclick = function() {
    selectBucket(resultBucketIndex - 1);
  };
  previousLink.appendChild(previousIcon);
  previous.appendChild(previousLink);
  if(resultBucketIndex == 0){
    previous.className += 'disabled'
  }

  ul.appendChild(previous);

  // Add position counter
  var counterSpan = document.createElement('span');
  var lowerLimit = (resultBucketIndex * resultBucketSize) + 1;
  var upperLimit = lowerLimit + resultBuckets[resultBucketIndex].length - 1;
  var counterText = ' ' + lowerLimit + ' - ' + upperLimit + ' / ' + entityCount + ' ';
  var counterLink = document.createElement('a');
  var counterIcon = document.createElement('i');
  counterSpan.appendChild(document.createTextNode(counterText));
  counterIcon.className += "fa fa-search";
  counterIcon.setAttribute('area-hidden', true);
  counterLink.href = arachneQueryUrl.replace('data', '') + zenonId;
  counterLink.target = '_blank';
  counterLink.appendChild(counterIcon);
  counterSpan.appendChild(counterLink);
  counterSpan.appendChild(document.createTextNode(' '));

  ul.append(counterSpan);

  // Add arrow right
  var next = document.createElement('li');
  var nextLink = document.createElement('a');
  var nextIcon = document.createElement('i');
  nextIcon.className += "fa fa-arrow-right";
  nextIcon.setAttribute('area-hidden', true);
  nextLink.onclick = function() {
    selectBucket(resultBucketIndex + 1);
  };
  nextLink.appendChild(nextIcon);
  next.appendChild(nextLink);
  if(resultBucketIndex == resultBuckets.length - 1){
    next.className += 'disabled'
  }
  ul.appendChild(next);

  nav.appendChild(ul);
  return nav;
}

function addArachneEntityLink(entity) {
  var div = document.createElement('div');
  var a = document.createElement('a');
  a.href = entity['@id'];
  a.target='_blank';

  var icon = document.createElement('i');
  var textElement = document.createTextNode(entity['title']);
  var subtitleElement = null;
  if(entity['subtitle']) {
    var subtitleText = document.createTextNode(" | " + entity['subtitle']);
    subtitleElement = document.createElement('small');
    subtitleElement.appendChild(subtitleText);
  }

  icon.className += 'fa fa-university';
  icon.setAttribute('area-hidden', 'true');

  a.appendChild(icon);
  a.appendChild(textElement);
  div.appendChild(a);
  if(subtitleElement != null){
    div.appendChild(subtitleElement);
  }

  return div;
}
