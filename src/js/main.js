import { initHeader } from './modules/header.js';
import { initAccordions } from './modules/accordion.js';
import { initDragSliders } from './modules/drag-slider.js';
import { initTitleReveal } from './modules/title-reveal.js';
import { initSachetJourney } from './modules/sachet-journey.js';
import { initProductSliders } from './modules/product-slider.js';
import { initResearch } from './modules/research.js';
import { initCardsRise } from './modules/cards-rise.js';
import { initSwipeButtons } from './modules/swipe-button.js';

document.addEventListener('DOMContentLoaded', () => {
  initSwipeButtons();
  initHeader();
  initAccordions();
  initDragSliders();
  initTitleReveal();
  initSachetJourney();
  initProductSliders();
  initResearch();
  initCardsRise();
});
