var defaultDescriptionLink;
var plusDescriptionLink;

var defaultDescription;
var plusDescription;

window.onload = setup();

function setup() {
    defaultDescriptionLink = document.querySelector('#show-default-description');
    plusDescriptionLink = document.querySelector('#show-plus-description');

    defaultDescription = document.querySelector('#default-description');
    plusDescription = document.querySelector('#plus-description');

    if(document.querySelector('#combined-index-info') === null) showDefaultDescription();
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