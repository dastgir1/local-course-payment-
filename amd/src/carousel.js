// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course carousel behaviour.
 *
 * @module     local_course_carousel/carousel
 * @copyright  2026 Syed Ghulam Dastgir <ghulam.dastgir@paktaleem.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        init: function() {
            const carousel = document.querySelector("#carouselExampleAutoplaying");
            if (!carousel) {
                return;
            }

            const carouselInner = carousel.querySelector(".carousel-inner");
            const prevButton = carousel.querySelector(".carousel-control-prev");
            const nextButton = carousel.querySelector(".carousel-control-next");
            if (!carouselInner || !prevButton || !nextButton) {
                return;
            }

            const cards = Array.from(carouselInner.querySelectorAll(".card"));
            if (cards.length === 0) {
                return;
            }

            const uniqueCards = cards.filter((card, index, list) => {
                const imageSrc = card.querySelector("img")?.getAttribute("src");
                return list.findIndex((item) => item.querySelector("img")?.getAttribute("src") === imageSrc) === index;
            });
            const visibleCards = 3;
            const slideDelay = 3000;
            let currentIndex = 0;
            let autoSlide;

            carouselInner.innerHTML = "";

            const cardWrapper = document.createElement("div");
            cardWrapper.className = "card-wraper";

            uniqueCards.forEach((card) => {
                cardWrapper.appendChild(card.cloneNode(true));
            });

            uniqueCards.slice(0, visibleCards).forEach((card) => {
                cardWrapper.appendChild(card.cloneNode(true));
            });

            carouselInner.appendChild(cardWrapper);

            const moveSlider = () => {
                const firstCard = cardWrapper.querySelector(".card");
                if (!firstCard) {
                    return;
                }
                const cardWidth = firstCard.offsetWidth;
                const gap = parseFloat(getComputedStyle(cardWrapper).gap) || 0;
                cardWrapper.style.transform = `translateX(-${currentIndex * (cardWidth + gap)}px)`;
            };

            const nextSlide = () => {
                currentIndex++;
                cardWrapper.style.transition = "transform .5s ease";
                moveSlider();
            };

            const prevSlide = () => {
                if (currentIndex === 0) {
                    currentIndex = uniqueCards.length;
                    cardWrapper.style.transition = "none";
                    moveSlider();
                }

                requestAnimationFrame(() => {
                    currentIndex--;
                    cardWrapper.style.transition = "transform .5s ease";
                    moveSlider();
                });
            };

            const startAutoSlide = () => {
                autoSlide = setInterval(nextSlide, slideDelay);
            };

            const resetAutoSlide = () => {
                clearInterval(autoSlide);
                startAutoSlide();
            };

            nextButton.addEventListener("click", () => {
                nextSlide();
                resetAutoSlide();
            });

            prevButton.addEventListener("click", () => {
                prevSlide();
                resetAutoSlide();
            });

            cardWrapper.addEventListener("transitionend", () => {
                if (currentIndex === uniqueCards.length) {
                    currentIndex = 0;
                    cardWrapper.style.transition = "none";
                    moveSlider();
                }
            });

            window.addEventListener("resize", moveSlider);
            startAutoSlide();
        }
    };
});