(function() {
  'use strict';
  var RM = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* DOM refs */
  var journey = document.getElementById('hiw-journey');
  var stage = document.getElementById('hiw-stage');
  var steps = [].slice.call(document.querySelectorAll('.hiw-step'));
  var fill = document.getElementById('hiw-stepsFill');
  var phaseEl = document.getElementById('hiw-phase');

  var scene0 = document.getElementById('hiw-scene0');
  var scene1 = document.getElementById('hiw-scene1');
  var scene2 = document.getElementById('hiw-scene2');
  var scene3 = document.getElementById('hiw-scene3');
  var scenes = [scene0, scene1, scene2, scene3];

  var s0Badge = document.getElementById('hiw-s0Badge');
  var s0Hero = document.getElementById('hiw-s0Hero');
  var s0Details = document.getElementById('hiw-s0Details');
  var s0DetailItems = [].slice.call(document.querySelectorAll('.hiw-s0-detail'));

  var s2Badge = document.getElementById('hiw-s2Badge');
  var s2MapCard = document.getElementById('hiw-s2MapCard');
  var s2Plane = document.getElementById('hiw-s2Plane');
  var s2MapFooter = document.getElementById('hiw-s2MapFooter');
  var s2QuoteCard = document.getElementById('hiw-s2QuoteCard');

  var s3Badge = document.getElementById('hiw-s3Badge');
  var s3Main = document.getElementById('hiw-s3Main');
  var s3TicketCard = document.getElementById('hiw-s3TicketCard');
  var s3BaggageCard = document.getElementById('hiw-s3BaggageCard');
  var s3Bottom = document.querySelector('.hiw-s3-bottom');
  var confetti = [].slice.call(document.querySelectorAll('[data-confetti]'));

  var tiles = [].slice.call(document.querySelectorAll('[data-tile]'));
  var s4Plane = document.getElementById('hiw-s4Plane');
  var s4Finale = document.getElementById('hiw-s4Finale');
  var magBtn = document.getElementById('hiw-magBtn');

  var CAPTIONS = ['Departure lounge', 'In the air', 'Arrival', 'The journey'];
  var WIN = [[0.0, 0.25], [0.25, 0.5], [0.5, 0.75], [0.75, 1.0]];
  var TILE_DEPTHS = [-120, -190, -70, -150, -100, -220];
  var TILE_ROTS = [-3, 3, 1.5, -2, 2.5, -1.5];

  /* Leaflet route data */
  var ROUTE_COORDS = [
    [28.5562, 77.1],
    [30.5, 69.0],
    [28.5, 55.0],
    [25.25, 55.36],
    [35.0, 40.0],
    [40.0, 25.0],
    [49.01, 2.55],
    [48.0, -10.0],
    [45.0, -30.0],
    [42.0, -50.0],
    [40.64, -73.78]
  ];
  var leafletMap = null;
  var routeActivePoly = null;
  var routeGlowPoly = null;
  var routeBgPoly = null;
  var routeTotalDist = 0;
  var routeSegDists = [];

  /* helpers */
  function cl(v, a, b) { return v < a ? a : v > b ? b : v; }
  function n01(v) { return cl(v, 0, 1); }
  function eOut(t) { return 1 - Math.pow(1 - t, 3); }
  function eIO(t) { return t < .5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2; }
  function lerp(a, b, t) { return a + (b - a) * t; }
  function px(v) { return v.toFixed(2) + 'px'; }
  function rand(seed) {
    var x = Math.sin(seed * 127.1 + 311.7) * 43758.5453;
    return x - Math.floor(x);
  }
  function localT(t, start, dur) { return n01((t - start) / dur); }
  function sceneOpacity(p, winStart, winEnd) {
    var w = winEnd - winStart;
    var t = (p - winStart) / w;
    if (t < 0.1) return t / 0.1;
    if (t > 0.88) return Math.max(0, (1 - t) / 0.12);
    return 1;
  }

  /* Leaflet map init */
  function initLeafletMap() {
    if (leafletMap || typeof L === 'undefined') return;
    var container = document.getElementById('hiwLeafletMap');
    if (!container || container._leaflet_id) return;

    leafletMap = L.map('hiwLeafletMap', {
      center: [35, 5],
      zoom: 2,
      minZoom: 2,
      maxZoom: 4,
      zoomControl: false,
      attributionControl: false,
      scrollWheelZoom: false,
      dragging: false,
      touchZoom: false,
      doubleClickZoom: false,
      boxZoom: false,
      keyboard: false
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
      maxZoom: 19
    }).addTo(leafletMap);

    routeBgPoly = L.polyline(ROUTE_COORDS, {
      color: 'rgba(0,58,89,0.12)', weight: 2, dashArray: '6,8', interactive: false
    }).addTo(leafletMap);

    routeActivePoly = L.polyline([ROUTE_COORDS[0]], {
      color: '#B4894F', weight: 4, lineCap: 'round', interactive: false
    }).addTo(leafletMap);

    routeGlowPoly = L.polyline([ROUTE_COORDS[0]], {
      color: 'rgba(180,137,79,0.3)', weight: 10, lineCap: 'round', interactive: false
    }).addTo(leafletMap);

    var cityMarkers = [
      { latlng: [28.5562, 77.1], color: '#003A59', code: 'DEL', name: 'Delhi' },
      { latlng: [25.25, 55.36], color: '#B4894F', code: 'DXB', name: 'Dubai' },
      { latlng: [49.01, 2.55], color: '#B4894F', code: 'CDG', name: 'Paris' },
      { latlng: [40.64, -73.78], color: '#003A59', code: 'JFK', name: 'New York' }
    ];

    cityMarkers.forEach(function(m) {
      L.circleMarker(m.latlng, { radius: 12, color: m.color, fillColor: m.color, fillOpacity: 0.12, weight: 0, interactive: false }).addTo(leafletMap);
      L.circleMarker(m.latlng, { radius: 5, color: '#fff', fillColor: m.color, fillOpacity: 1, weight: 2.5, interactive: false }).addTo(leafletMap);
      var icon = L.divIcon({
        className: '',
        html: '<div style="display:inline-flex;flex-direction:column;align-items:center;background:rgba(255,255,255,0.88);backdrop-filter:blur(4px);padding:3px 8px;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,0.1);transform:translate(-50%,8px);white-space:nowrap;"><span style="font-family:Inter,sans-serif;font-size:11px;font-weight:700;color:' + m.color + ';letter-spacing:0.1em;line-height:1.1;">' + m.code + '</span><span style="font-family:Inter,sans-serif;font-size:8.5px;font-weight:500;color:#666;line-height:1.1;">' + m.name + '</span></div>',
        iconSize: [0, 0],
        iconAnchor: [0, 0]
      });
      L.marker(m.latlng, { icon: icon, interactive: false }).addTo(leafletMap);
    });

    routeSegDists = [];
    routeTotalDist = 0;
    for (var i = 1; i < ROUTE_COORDS.length; i++) {
      var d = leafletMap.distance(L.latLng(ROUTE_COORDS[i - 1]), L.latLng(ROUTE_COORDS[i]));
      routeSegDists.push(d);
      routeTotalDist += d;
    }

    setTimeout(function() { leafletMap.invalidateSize(); }, 200);
  }

  /* get point along route at progress */
  function getRoutePoint(progress) {
    if (!leafletMap || routeTotalDist <= 0) return null;
    var targetDist = progress * routeTotalDist;
    var accum = 0;
    for (var i = 0; i < routeSegDists.length; i++) {
      if (accum + routeSegDists[i] >= targetDist) {
        var frac = (targetDist - accum) / routeSegDists[i];
        var lat = ROUTE_COORDS[i][0] + (ROUTE_COORDS[i + 1][0] - ROUTE_COORDS[i][0]) * frac;
        var lng = ROUTE_COORDS[i][1] + (ROUTE_COORDS[i + 1][1] - ROUTE_COORDS[i][1]) * frac;
        var ang = Math.atan2(
          ROUTE_COORDS[i + 1][0] - ROUTE_COORDS[i][0],
          ROUTE_COORDS[i + 1][1] - ROUTE_COORDS[i][1]
        );
        return { latlng: [lat, lng], angle: ang };
      }
      accum += routeSegDists[i];
    }
    return { latlng: ROUTE_COORDS[ROUTE_COORDS.length - 1], angle: 0 };
  }

  /* scroll calc */
  function getProgress() {
    if (!journey) return 0;
    var rect = journey.getBoundingClientRect();
    var scrolled = -rect.top;
    var total = rect.height - window.innerHeight;
    return total > 0 ? cl(scrolled / total, 0, 1) : 0;
  }

  /* update steps */
  function updateSteps(p) {
    var activeIdx = 0;
    for (var i = 0; i < WIN.length; i++) {
      if (p >= WIN[i][0] && p < WIN[i][1]) { activeIdx = i; break; }
      if (p >= WIN[i][1]) activeIdx = i + 1;
    }
    activeIdx = cl(activeIdx, 0, 3);

    if (phaseEl) phaseEl.textContent = CAPTIONS[activeIdx];

    if (fill) {
      var h = (p / 1) * 100;
      fill.style.transform = 'scaleY(' + cl(h / 100, 0, 1) + ')';
    }

    steps.forEach(function(s, i) {
      var state = i < activeIdx ? 'past' : i === activeIdx ? 'active' : 'future';
      s.setAttribute('data-state', state);
    });
  }

  /* Scene 0: Plan — Professional Redesign */
  function renderScene0(t) {
    /* Badge — slides down */
    if (s0Badge) {
      var badgeT = localT(t, 0.01, 0.18);
      s0Badge.style.transform = 'translateX(-50%) translate3d(0,' + px(lerp(-14, 0, badgeT)) + ',0)';
      s0Badge.style.opacity = badgeT.toFixed(3);
    }

    /* Hero card — fades in with slight rise */
    if (s0Hero) {
      s0Hero.style.transform = 'translate3d(0,' + px(lerp(24, -24, t)) + ',0)';
      s0Hero.style.opacity = sceneOpacity(t, 0, 1).toFixed(3);
    }

    /* Details row — cards slide up */
    if (s0Details) {
      var detT = localT(t, 0.08, 0.4);
      s0Details.style.transform = 'translate3d(0,' + px(lerp(18, 0, detT)) + ',0)';
    }

    s0DetailItems.forEach(function(d, i) {
      var start = 0.12 + i * 0.1;
      var dt = localT(t, start, 0.14);
      d.style.transform = 'translate3d(0,' + px(lerp(14, 0, dt)) + ',0)';
      d.style.opacity = dt.toFixed(3);
    });
  }

  /* Scene 1: Quote — Professional Redesign */
  function renderScene1(t) {
    /* Invalidate Leaflet map when this scene becomes visible */
    if (t > 0.05 && leafletMap && !window._hiwMapValidated) {
      window._hiwMapValidated = true;
      setTimeout(function() { if (leafletMap) leafletMap.invalidateSize(); }, 200);
    }
    /* Reset flag when leaving scene so it re-fires on return */
    if (t < 0.01 && window._hiwMapValidated) {
      window._hiwMapValidated = false;
    }

    /* Badge — slides down from top */
    if (s2Badge) {
      var badgeT = localT(t, 0.01, 0.18);
      s2Badge.style.transform = 'translateX(-50%) translate3d(0,' + px(lerp(-16, 0, badgeT)) + ',0)';
      s2Badge.style.opacity = badgeT.toFixed(3);
    }

    /* Map card — fades in with slight vertical movement */
    if (s2MapCard) {
      s2MapCard.style.transform = 'translate3d(0,' + px(lerp(30, -20, t)) + ',0)';
      s2MapCard.style.opacity = sceneOpacity(t, 0, 1).toFixed(3);
    }

    /* Map footer — fades in after map */
    if (s2MapFooter) {
      var footerT = localT(t, 0.12, 0.2);
      s2MapFooter.style.transform = 'translate3d(0,' + px(lerp(8, 0, footerT)) + ',0)';
      s2MapFooter.style.opacity = footerT.toFixed(3);
    }

    /* Leaflet route animation */
    if (leafletMap && routeActivePoly && routeGlowPoly && routeTotalDist > 0) {
      var drawT = localT(t, 0.1, 0.75);
      var activeCoords = [];
      var accum = 0;
      var targetDist = drawT * routeTotalDist;
      activeCoords.push(ROUTE_COORDS[0]);
      for (var i = 0; i < routeSegDists.length; i++) {
        if (accum + routeSegDists[i] < targetDist) {
          activeCoords.push(ROUTE_COORDS[i + 1]);
        } else {
          var frac = (targetDist - accum) / routeSegDists[i];
          var lat = ROUTE_COORDS[i][0] + (ROUTE_COORDS[i + 1][0] - ROUTE_COORDS[i][0]) * frac;
          var lng = ROUTE_COORDS[i][1] + (ROUTE_COORDS[i + 1][1] - ROUTE_COORDS[i][1]) * frac;
          activeCoords.push([lat, lng]);
          break;
        }
        accum += routeSegDists[i];
      }
      routeActivePoly.setLatLngs(activeCoords);
      routeGlowPoly.setLatLngs(activeCoords);

      if (s2Plane) {
        var rp = getRoutePoint(drawT);
        if (rp && leafletMap) {
          var pt = leafletMap.latLngToContainerPoint(L.latLng(rp.latlng));
          s2Plane.style.left = pt.x + 'px';
          s2Plane.style.top = pt.y + 'px';
          s2Plane.style.transform = 'translate(-50%,-50%) rotate(' + (-rp.angle * 180 / Math.PI) + 'deg)';
          s2Plane.style.opacity = drawT > 0.05 ? '1' : '0';
        }
      }
    }

    /* Quote card — slides up from bottom */
    if (s2QuoteCard) {
      var quoteT = localT(t, 0.15, 0.45);
      s2QuoteCard.style.transform = 'translate3d(0,' + px(lerp(40, 0, quoteT)) + ',0)';
      s2QuoteCard.style.opacity = quoteT.toFixed(3);
    }
  }

  /* Scene 2: Confirm — Professional Redesign */
  function renderScene2(t) {
    /* Badge — slides down from top */
    if (s3Badge) {
      var badgeT = localT(t, 0.01, 0.18);
      s3Badge.style.transform = 'translateX(-50%) translate3d(0,' + px(lerp(-16, 0, badgeT)) + ',0)';
      s3Badge.style.opacity = badgeT.toFixed(3);
    }

    /* Main confirmation card — fades in with slight rise */
    if (s3Main) {
      s3Main.style.transform = 'translate3d(0,' + px(lerp(30, -30, t)) + ',0)';
      s3Main.style.opacity = sceneOpacity(t, 0, 1).toFixed(3);
    }

    /* Bottom cards — slide up from below */
    if (s3Bottom) {
      var bt = localT(t, 0.12, 0.4);
      s3Bottom.style.transform = 'translate3d(0,' + px(lerp(24, 0, bt)) + ',0)';
    }

    if (s3TicketCard) {
      var tt = localT(t, 0.18, 0.35);
      s3TicketCard.style.transform = 'translate3d(' + px(lerp(-20, 0, tt)) + ',0,0)';
      s3TicketCard.style.opacity = tt.toFixed(3);
    }

    if (s3BaggageCard) {
      var bt2 = localT(t, 0.22, 0.35);
      s3BaggageCard.style.transform = 'translate3d(' + px(lerp(20, 0, bt2)) + ',0,0)';
      s3BaggageCard.style.opacity = bt2.toFixed(3);
    }

    /* Confetti — celebration effect */
    confetti.forEach(function(c, i) {
      var st = 0.25 + rand(i) * 0.25;
      var ct2 = localT(t, st, 0.6);
      var cx = rand(i + 1) * 100;
      var cy = lerp(-10, 110, ct2);
      var cr = rand(i + 2) * 360;
      var co2 = ct2 < 0.1 ? ct2 / 0.1 : ct2 > 0.7 ? (1 - ct2) / 0.3 : 1;
      var hue = [35, 200, 45, 350, 210, 50][i % 6];
      c.style.cssText = 'left:' + cx + '%;top:' + cy + '%;transform:rotate(' + cr + 'deg);opacity:' + cl(co2, 0, 1).toFixed(3) + ';background:hsl(' + hue + ',70%,55%);';
    });
  }

  /* Scene 3: Travel */
  function renderScene3(t) {
    /* Tiles — scatter effect with depth */
    tiles.forEach(function(tile, i) {
      var depth = TILE_DEPTHS[i] || 0;
      var rot = TILE_ROTS[i] || 0;
      var y = depth * t;
      var r = rot * t;
      var zoom = lerp(1.18, 1, t);
      tile.style.transform = 'translate3d(0,' + px(y) + ',0) rotate(' + r.toFixed(2) + 'deg) scale(' + zoom.toFixed(4) + ')';
      tile.style.opacity = sceneOpacity(t, 0, 1).toFixed(3);
      tile.style.filter = 'blur(' + px(lerp(0, 5, n01(t * 0.8))) + ')';
    });

    /* Plane — flying across */
    if (s4Plane) {
      var planeX = lerp(-15, 110, t);
      var planeY = lerp(40, -20, t < 0.5 ? t * 2 : 2 - t * 2);
      s4Plane.style.left = planeX + '%';
      s4Plane.style.top = planeY + '%';
      s4Plane.style.opacity = sceneOpacity(t, 0, 1).toFixed(3);
    }

    /* Finale — slides up near the end */
    if (s4Finale) {
      var ft2 = localT(t, 0.55, 0.18);
      var fy = lerp(24, 0, localT(t, 0.55, 0.28));
      s4Finale.style.transform = 'translate3d(0,' + px(fy) + ',0)';
      s4Finale.style.opacity = ft2.toFixed(3);
    }
  }

  /* barcode */
  function buildBarcode() {
    var bc = document.getElementById('hiw-barcode');
    if (!bc) return;
    var html = '';
    for (var i = 0; i < 28; i++) {
      var h = 16 + Math.random() * 20;
      var w = 2 + Math.random() * 2;
      html += '<i style="height:' + h.toFixed(1) + 'px;width:' + w.toFixed(1) + 'px;"></i>';
    }
    bc.innerHTML = html;
  }

  /* renderers */
  var renderers = [renderScene0, renderScene1, renderScene2, renderScene3];

  function render(p) {
    updateSteps(p);
    for (var i = 0; i < scenes.length; i++) {
      var w = WIN[i];
      if (p < w[0] - 0.02 || p > w[1] + 0.02) {
        scenes[i].style.opacity = '0';
        continue;
      }
      var t = (p - w[0]) / (w[1] - w[0]);
      t = cl(t, 0, 1);
      scenes[i].style.opacity = '1';
      renderers[i](t);
    }
  }

  /* raf loop */
  var ticking = false;
  function tick() {
    render(getProgress());
    ticking = false;
  }
  function onScroll() {
    if (!ticking) { ticking = true; requestAnimationFrame(tick); }
  }

  /* magnetic button — smooth tracking, preserves CSS hover lift */
  if (magBtn) {
    magBtn.addEventListener('pointermove', function(e) {
      var rect = magBtn.getBoundingClientRect();
      var cx = rect.left + rect.width / 2;
      var cy = rect.top + rect.height / 2;
      var dx = (e.clientX - cx) * 0.14;
      var dy = (e.clientY - cy) * 0.14;
      /* Keep the CSS hover translateY(-3px) alive during magnetic tracking */
      magBtn.style.transform = 'translate3d(' + dx.toFixed(1) + 'px,' + (dy.toFixed(1) - 3) + 'px,0)';
      magBtn.style.willChange = 'transform';
    });
    magBtn.addEventListener('pointerleave', function() {
      magBtn.style.transform = '';
      magBtn.style.willChange = '';
    });
  }

  /* init */
  function init() {
    updateSteps(0);
    if (RM) {
      scenes.forEach(function(s, i) { s.style.opacity = i === 0 ? '1' : '0'; });
      renderScene0(1);
    } else {
      if (typeof L !== 'undefined') initLeafletMap();
      tick();
    }
  }

  window.addEventListener('load', function() {
    if (typeof L !== 'undefined') initLeafletMap();
  });
  window.addEventListener('scroll', onScroll, { passive: true });
  setTimeout(init, 100);
})();