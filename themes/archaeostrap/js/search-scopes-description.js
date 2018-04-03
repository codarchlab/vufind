var defaultDescriptionLink;
var plusDescriptionLink;

var defaultDescription;
var plusDescription;

document.onload = setup();

function setup() {
    defaultDescriptionLink = document.querySelector('#show-default-description');
    plusDescriptionLink = document.querySelector('#show-plus-description');

    defaultDescription = document.querySelector('#default-description');
    plusDescription = document.querySelector('#plus-description');

    var initialRequest = window.location.href;

    if(initialRequest.includes('hiddenFilters%5B%5D=collection%3A%22iDAI.bibliography%22')) showDefaultDescription();
    else showPlusDescription();
}

function showDefaultDescription() {
    defaultDescription.removeAttribute('hidden');
    defaultDescriptionLink.classList.add('active');

    plusDescription.setAttribute('hidden', true);
    plusDescriptionLink.classList.remove('active');
}

function showPlusDescription() {
    defaultDescription.setAttribute('hidden', true);
    defaultDescriptionLink.classList.remove('active');

    plusDescription.removeAttribute('hidden');
    plusDescriptionLink.classList.add('active');
}