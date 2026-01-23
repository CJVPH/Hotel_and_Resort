// Calculate price based on room type and number of guests
function calculatePrice() {
    let room = document.getElementById("room-regular")?.checked ? "Regular" :
               document.getElementById("room-deluxe")?.checked ? "Deluxe" :
               document.getElementById("room-vip")?.checked ? "VIP" : "";

    let guests = document.getElementById("guests")?.value || "";
    let price = 0;

    // Use dynamic prices fetched from server if available, otherwise fallback to defaults

    if (room && guests) {
        if (typeof window.roomPrices !== 'undefined' && window.roomPrices[room] && window.roomPrices[room][guests]) {
            price = Number(window.roomPrices[room][guests]);
        } else if (defaults[room] && defaults[room][guests]) {
            price = defaults[room][guests];
        }
    }

    // Add configuration add-ons (bedrooms, breakfast, etc.)
    let addonTotal = 0;
    const configContainer = document.getElementById('configurationOptions');
    if (configContainer) {
        // Support select[data-config="bedrooms"] (small groups)
        const bedroomSelect = configContainer.querySelector('select[data-config="bedrooms"]');
        if (bedroomSelect && bedroomSelect.value) {
            const option = bedroomSelect.options[bedroomSelect.selectedIndex];
            const addon = parseFloat(option.getAttribute('data-addon') || 0);
            if (!isNaN(addon)) addonTotal += addon;
        }

        // Support radio/card selection for house layouts (data-config on inputs) or hidden house_layout input
        const bedroomRadio = configContainer.querySelector('input[data-config="bedrooms"]:checked');
        if (bedroomRadio) {
            const addon = parseFloat(bedroomRadio.getAttribute('data-addon') || 0);
            if (!isNaN(addon)) addonTotal += addon;
        }

        // If no radio selected, check for hidden auto house_layout input
        if (!bedroomRadio) {
            const hiddenLayout = configContainer.querySelector('input[name="house_layout"]');
            if (hiddenLayout) {
                const addon = parseFloat(hiddenLayout.getAttribute('data-addon') || 0);
                if (!isNaN(addon)) addonTotal += addon;
            }
        }

        // Extras for house layouts
        const extras = configContainer.querySelectorAll('input[data-extra]');
        extras.forEach(cb => {
            if (cb.checked) {
                const ex = parseFloat(cb.getAttribute('data-addon') || 0);
                if (!isNaN(ex)) addonTotal += ex;
            }
        });

        const breakfastCheckbox = configContainer.querySelector('input[name="breakfast"]');
        if (breakfastCheckbox && breakfastCheckbox.checked) {
            const perPax = parseFloat(breakfastCheckbox.getAttribute('data-perpax') || 0);
            const paxCount = parseInt(guests) || 0;
            if (!isNaN(perPax) && paxCount > 0) addonTotal += perPax * paxCount;
        }
    }

    price = Number(price) + Number(addonTotal);

    // Update price display
    const priceInput = document.getElementById("price");
    const priceDisplay = document.getElementById("price-display");
    
    if (priceInput) {
        priceInput.value = price;
    }
    
    if (priceDisplay) {
        priceDisplay.textContent = price.toLocaleString();
    }

    // Ensure options hidden input exists and update its value
    const form = document.getElementById('reservationForm') || document.querySelector('.reservation-form');
    if (form) {
        let optInput = form.querySelector('input[name="options"]');
        const config = {};
        const configContainer2 = document.getElementById('configurationOptions');
        if (configContainer2) {
            // select-based choice
            const bedroomSelect = configContainer2.querySelector('select[data-config="bedrooms"]');
            if (bedroomSelect) config.bedrooms = bedroomSelect.value || null;

            // radio/card choice (house layouts) or hidden auto house_layout
            const bedroomRadio = configContainer2.querySelector('input[data-config="bedrooms"]:checked');
            if (bedroomRadio) config.house_layout = bedroomRadio.value || null;
            else {
                const hiddenLayout = configContainer2.querySelector('input[name="house_layout"]');
                if (hiddenLayout) config.house_layout = hiddenLayout.value || null;
            }

            // extras
            const extras = {};
            configContainer2.querySelectorAll('input[data-extra]').forEach(cb => {
                extras[cb.getAttribute('data-extra')] = !!cb.checked;
            });
            if (Object.keys(extras).length) config.extras = extras;

            const breakfastCheckbox = configContainer2.querySelector('input[name="breakfast"]');
            if (breakfastCheckbox) config.breakfast = breakfastCheckbox.checked;
        }

        if (!optInput) {
            optInput = document.createElement('input');
            optInput.type = 'hidden';
            optInput.name = 'options';
            form.appendChild(optInput);
        }
        optInput.value = JSON.stringify(config);
    }
}

// Select room type when clicking on room card
async function selectRoom(roomType) {
    const radioId = `room-${roomType.toLowerCase()}`;
    const radio = document.getElementById(radioId);
    if (radio) {
        radio.checked = true;
        await displayRoomPreview(roomType);
        calculatePrice();
    }
}

// Fetch room images from server
async function fetchRoomImages(roomType) {
    try {
        const response = await fetch(`api/get_room_images.php?room_type=${roomType}`);
        const data = await response.json();
        return data.images || [];
    } catch (error) {
        console.error('Error fetching room images:', error);
        return [];
    }
}

// Display room preview on the right side (text only, no images)
async function displayRoomPreview(roomType) {
    const previewContainer = document.getElementById('roomPreview');
    const roomData = roomDetails[roomType];

    if (!roomData || !previewContainer) return;

    // Fetch images for this room type (uploaded via admin)
    let images = [];
    try {
        images = await fetchRoomImages(roomType);
    } catch (err) {
        console.warn('Error fetching images for', roomType, err);
        images = [];
    }

    // Create all amenities
    let amenitiesHTML = '';
    if (roomData.amenities && roomData.amenities.length > 0) {
        amenitiesHTML = '<div class="room-preview-amenities"><h4><i class="fas fa-star"></i> Amenities</h4><div class="amenities-preview-grid">';
        roomData.amenities.forEach(amenity => {
            amenitiesHTML += `
                <div class="amenity-preview-item">
                    <i class="fas ${amenity.icon}"></i>
                    <span>${amenity.text}</span>
                </div>
            `;
        });
        amenitiesHTML += '</div></div>';
    }

    // Create all inclusions
    let inclusionsHTML = '';
    if (roomData.inclusions && roomData.inclusions.length > 0) {
        inclusionsHTML = '<div class="room-preview-inclusions"><h4><i class="fas fa-gift"></i> Included Services</h4><ul class="inclusions-preview-list">';
        roomData.inclusions.forEach(inclusion => {
            inclusionsHTML += `<li><i class="fas fa-check-circle"></i> ${inclusion}</li>`;
        });
        inclusionsHTML += '</ul></div>';
    }

    // Build images HTML if available
    let imagesHTML = '';
    if (images && images.length) {
        imagesHTML = '<div class="room-images-grid">';
        images.forEach(src => {
            // Ensure safe src (trusting server-sent path relative to webroot)
            imagesHTML += `\n                <div class="room-image-item">\n                    <img src="${src}" alt="${roomType} image">\n                </div>`;
        });
        imagesHTML += '\n</div>';
    }

    previewContainer.innerHTML = `
        <div class="room-preview-card">
            <div class="room-preview-title">
                <h3>${roomData.title}</h3>
                <p class="room-preview-desc">${roomData.description}</p>
            </div>
            ${imagesHTML}
            ${amenitiesHTML}
            ${inclusionsHTML}
        </div>
    `;
}

// Room details data
const roomDetails = {
    Regular: {
        title: "Regular Room",
        description: "Comfortable and affordable accommodation perfect for budget-conscious travelers. Enjoy a cozy stay with all essential amenities.",
        amenities: [
            { icon: "fa-bed", text: "Comfortable Queen Size Bed" },
            { icon: "fa-tv", text: "32-inch LED TV" },
            { icon: "fa-wifi", text: "Free High-Speed WiFi" },
            { icon: "fa-snowflake", text: "Air Conditioning" },
            { icon: "fa-shower", text: "Private Bathroom with Hot Shower" },
            { icon: "fa-coffee", text: "Coffee/Tea Making Facilities" },
            { icon: "fa-lock", text: "In-Room Safe" },
            { icon: "fa-phone", text: "Direct Dial Telephone" }
        ],
        inclusions: [
            "Welcome drink upon arrival",
            "Daily housekeeping service",
            "Complimentary toiletries",
            "Free parking",
            "24/7 front desk service",
            "Access to hotel facilities"
        ]
    },
    Deluxe: {
        title: "Deluxe Room",
        description: "Spacious and elegantly designed rooms with premium amenities. Experience enhanced comfort and luxury during your stay.",
        amenities: [
            { icon: "fa-bed", text: "King Size Bed with Premium Linens" },
            { icon: "fa-tv", text: "43-inch Smart TV" },
            { icon: "fa-wifi", text: "Free High-Speed WiFi" },
            { icon: "fa-snowflake", text: "Climate Control System" },
            { icon: "fa-bath", text: "Spacious Bathroom with Bathtub" },
            { icon: "fa-coffee", text: "Premium Coffee/Tea Set" },
            { icon: "fa-couch", text: "Sitting Area with Sofa" },
            { icon: "fa-lock", text: "Electronic Safe" },
            { icon: "fa-utensils", text: "Mini Bar" },
            { icon: "fa-mountain", text: "Balcony with City View" }
        ],
        inclusions: [
            "Welcome drink and fruit basket",
            "Daily housekeeping service",
            "Premium toiletries and bathrobes",
            "Free parking",
            "24/7 concierge service",
            "Access to fitness center",
            "Complimentary breakfast",
            "Room service available"
        ]
    },
    VIP: {
        title: "VIP Suite",
        description: "Ultimate luxury and sophistication. Our VIP suites offer the finest accommodations with exclusive amenities and personalized service.",
        amenities: [
            { icon: "fa-bed", text: "King Size Four-Poster Bed" },
            { icon: "fa-tv", text: "55-inch 4K Smart TV" },
            { icon: "fa-wifi", text: "Premium High-Speed WiFi" },
            { icon: "fa-snowflake", text: "Smart Climate Control" },
            { icon: "fa-bath", text: "Luxury Spa Bathroom with Jacuzzi" },
            { icon: "fa-coffee", text: "Nespresso Coffee Machine" },
            { icon: "fa-couch", text: "Separate Living Area" },
            { icon: "fa-lock", text: "Premium Electronic Safe" },
            { icon: "fa-utensils", text: "Fully Stocked Mini Bar" },
            { icon: "fa-mountain", text: "Private Balcony with Panoramic View" },
            { icon: "fa-spa", text: "In-Room Spa Facilities" },
            { icon: "fa-headphones", text: "Premium Sound System" }
        ],
        inclusions: [
            "Champagne welcome and gourmet treats",
            "Butler service available",
            "Daily housekeeping with turndown service",
            "Premium toiletries and luxury bathrobes",
            "Complimentary valet parking",
            "24/7 dedicated concierge",
            "Access to VIP lounge",
            "Full access to spa and wellness center",
            "Complimentary breakfast and afternoon tea",
            "Priority restaurant reservations",
            "Airport transfer service (on request)",
            "Personalized room preferences"
        ]
    }
};

// Default fallback prices (if API not available)
const defaults = {
    Regular: {2:1500, 8:3000, 20:6000},
    Deluxe:  {2:2500, 8:4500, 20:8500},
    VIP:     {2:4000, 8:7000, 20:12000}
};

// Configuration options and add-on pricing for multi-pax groups
const optionsConfig = {
    '8': {
        bedrooms: [
            { value: '2_bedrooms', label: '2 Bedrooms', addon: 1000 },
            { value: '4_bedrooms', label: '4 Bedrooms', addon: 2000 },
            { value: '2_big_bedrooms', label: '2 Big Bedrooms', addon: 1500 }
        ],
        breakfastPerPax: 200
    },
    '20': {
        // For large groups we present house-style layouts (visual cards)
        layouts: [
            { value: 'villa_5br', label: 'Villa — 5 BR / 3 BA', addon: 5000, desc: 'Comfortable villa with 5 bedrooms, 3 bathrooms, living area and kitchen.' },
            { value: 'compound_units', label: 'Compound — Multiple Units', addon: 7000, desc: 'Several adjoining units suitable for groups, shared garden and common area.' },
            { value: 'mansion_10br', label: 'Mansion — 10 BR', addon: 12000, desc: 'Large mansion-style house with many bedrooms and private amenities.' }
        ],
        extras: [
            { name: 'private_pool', label: 'Private Pool', addon: 3000 },
            { name: 'private_chef', label: 'Private Chef (per day)', addon: 2500 }
        ],
        breakfastPerPax: 200
    }
};

// Render configuration options UI depending on selected guests
function renderConfigurationOptions() {
    const guests = document.getElementById('guests')?.value;
    const container = document.getElementById('configurationOptions');
    if (!container) return;

    // Clear existing
    container.innerHTML = '';

    if (!guests || !(guests in optionsConfig)) return;

    const cfg = optionsConfig[guests];

    // If this is the 20-pax (house) configuration, render house-layout cards and extras
    if (guests === '20' && cfg.layouts) {
        // Inject minimal CSS for house cards if not present
        if (!document.getElementById('house-card-styles')) {
            const style = document.createElement('style');
            style.id = 'house-card-styles';
            style.textContent = `
                .house-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-top:0.75rem; }
                .house-card { background: #fff; border: 1px solid #e6e6f2; padding: 1rem; border-radius: 10px; cursor: pointer; box-shadow: 0 8px 18px rgba(102,118,234,0.06); }
                .house-card h4 { margin: 0 0 0.25rem 0; color: #333; }
                .house-card p.muted { margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem; }
                .house-card .price-tag { font-weight: 700; color: #667eea; }
                .house-card.selected { border-color: #667eea; box-shadow: 0 10px 30px rgba(102,118,234,0.12); }
            `;
            document.head.appendChild(style);
        }
        // Automatically select the Villa layout for 10-20 pax (no choice)
        const villa = cfg.layouts.find(l => l.value && l.value.startsWith('villa')) || cfg.layouts[0];
        const summary = document.createElement('div');
        summary.className = 'form-group';
        const sTitle = document.createElement('label');
        sTitle.innerHTML = '<i class="fas fa-home"></i> Accommodation (auto-selected)';
        const sBody = document.createElement('div');
        sBody.style.padding = '0.75rem';
        sBody.style.background = '#fff';
        sBody.style.border = '1px solid #eee';
        sBody.style.borderRadius = '8px';
        sBody.innerHTML = `<strong>${villa.label}</strong><div class="muted" style="margin-top:0.25rem">${villa.desc}</div><div style="margin-top:0.5rem; font-weight:700; color:#667eea">+₱${Number(villa.addon).toLocaleString()}</div>`;

        // Hidden input to store the selected layout and addon
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'house_layout';
        hidden.value = villa.value;
        hidden.setAttribute('data-addon', villa.addon);
        hidden.setAttribute('data-config', 'bedrooms');

        summary.appendChild(sTitle);
        summary.appendChild(sBody);
        summary.appendChild(hidden);
        container.appendChild(summary);

        // Extras checkboxes (private pool, chef, etc.) - still available
        if (cfg.extras && cfg.extras.length) {
            const extrasDiv = document.createElement('div');
            extrasDiv.className = 'form-group';
            const extrasLabel = document.createElement('label');
            extrasLabel.innerHTML = '<i class="fas fa-plus-circle"></i> Additional Services';
            extrasDiv.appendChild(extrasLabel);

            cfg.extras.forEach(ex => {
                const wrapper = document.createElement('div');
                wrapper.style.marginTop = '0.5rem';
                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.name = ex.name;
                cb.setAttribute('data-extra', ex.name);
                cb.setAttribute('data-addon', ex.addon);
                cb.id = 'extra_' + ex.name;
                cb.addEventListener('change', calculatePrice);

                const lbl = document.createElement('label');
                lbl.setAttribute('for', cb.id);
                lbl.style.marginLeft = '0.5rem';
                lbl.textContent = ex.label + ' (+₱' + Number(ex.addon).toLocaleString() + ')';

                wrapper.appendChild(cb);
                wrapper.appendChild(lbl);
                extrasDiv.appendChild(wrapper);
            });

            container.appendChild(extrasDiv);
        }

    } else {
        // Bedrooms select (for smaller groups like 4-8)
        if (cfg.bedrooms && cfg.bedrooms.length > 0) {
            const div = document.createElement('div');
            div.className = 'form-group';
            const label = document.createElement('label');
            label.innerHTML = '<i class="fas fa-door-open"></i> Bedroom Configuration';
            div.appendChild(label);

            const select = document.createElement('select');
            select.setAttribute('data-config', 'bedrooms');
            select.className = 'form-control';
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = 'Select configuration (optional)';
            select.appendChild(defaultOpt);

            cfg.bedrooms.forEach(b => {
                const o = document.createElement('option');
                o.value = b.value;
                o.textContent = `${b.label} (+₱${b.addon.toLocaleString()})`;
                o.setAttribute('data-addon', b.addon);
                select.appendChild(o);
            });

            select.addEventListener('change', calculatePrice);
            div.appendChild(select);
            container.appendChild(div);
        }

        // Breakfast checkbox
        if (cfg.breakfastPerPax) {
            const div2 = document.createElement('div');
            div2.className = 'form-group';
            const label2 = document.createElement('label');
            label2.innerHTML = '<i class="fas fa-utensils"></i> Include Breakfast (₱' + cfg.breakfastPerPax + ' per pax)';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'breakfast';
            checkbox.setAttribute('data-perpax', cfg.breakfastPerPax);
            checkbox.style.marginLeft = '0.5rem';
            checkbox.addEventListener('change', calculatePrice);
            div2.appendChild(label2);
            div2.appendChild(checkbox);
            container.appendChild(div2);
        }
    }
}

// Validate check-in and check-out dates
function validateDates() {
    const checkin = document.getElementById("checkin");
    const checkout = document.getElementById("checkout");
    
    if (checkin && checkout && checkin.value && checkout.value) {
        const checkinDate = new Date(checkin.value);
        const checkoutDate = new Date(checkout.value);
        
        // Set minimum checkout date to day after checkin
        if (checkoutDate <= checkinDate) {
            const nextDay = new Date(checkinDate);
            nextDay.setDate(nextDay.getDate() + 1);
            checkout.value = nextDay.toISOString().split('T')[0];
            
            // Show warning
            if (!document.querySelector('.date-warning')) {
                const warning = document.createElement('div');
                warning.className = 'alert alert-error date-warning';
                warning.innerHTML = '<i class="fas fa-exclamation-circle"></i> Check-out date must be after check-in date. Date adjusted automatically.';
                checkout.parentElement.appendChild(warning);
                
                setTimeout(() => {
                    warning.remove();
                }, 5000);
            }
        }
        
        // Calculate number of nights and update price if needed
        const nights = Math.ceil((checkoutDate - checkinDate) / (1000 * 60 * 60 * 24));
        if (nights > 0) {
            calculatePrice();
        }
    }
}

// Set minimum date for date inputs
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    
    if (checkinInput) {
        checkinInput.setAttribute('min', today);
    }
    
    if (checkoutInput) {
        checkoutInput.setAttribute('min', today);
    }
    
    // Add event listeners for room selection
    const roomRadios = document.querySelectorAll('input[name="room"]');
    roomRadios.forEach(radio => {
        radio.addEventListener('change', calculatePrice);
    });
    
    // Add event listener for guests select
    const guestsSelect = document.getElementById('guests');
    if (guestsSelect) {
        guestsSelect.addEventListener('change', function() { renderConfigurationOptions(); calculatePrice(); });
        // Render initial options (if any)
        renderConfigurationOptions();
    }
    
    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Fetch current room prices from API and store in window.roomPrices
document.addEventListener('DOMContentLoaded', function() {
    fetch('api/get_room_prices.php')
        .then(resp => resp.json())
        .then(data => {
            if (data && data.success && data.prices) {
                window.roomPrices = data.prices;
                calculatePrice();
            }
        })
        .catch(err => {
            console.warn('Could not fetch room prices, using defaults.', err);
        });
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.reservation-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Recalculate price and ensure options are serialized before final validation/submission
            calculatePrice();
            // If user is not logged in, redirect to login page instead of submitting
            if (typeof window.isLoggedIn !== 'undefined' && !window.isLoggedIn) {
                e.preventDefault();
                // optionally include return URL if you want the user to come back after login
                window.location.href = 'login.php';
                return false;
            }
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            const room = document.querySelector('input[name="room"]:checked');
            const guests = document.getElementById('guests').value;
            const price = document.getElementById('price').value;
            
            if (!checkin || !checkout || !room || !guests || !price || price == 0) {
                e.preventDefault();
                alert('Please fill in all fields and select a room type to calculate the price.');
                return false;
            }
            
            const checkinDate = new Date(checkin);
            const checkoutDate = new Date(checkout);
            
            if (checkoutDate <= checkinDate) {
                e.preventDefault();
                alert('Check-out date must be after check-in date.');
                return false;
            }
        });
    }
});

// Note: inline modal login removed; redirect to `login.php` to perform login.

