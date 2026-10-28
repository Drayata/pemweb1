/**
 * Integrasi Google Maps untuk Form Pengaduan dan Dashboard Admin.
 */

let complaintMap;
let complaintMarker;

/**
 * Mengisi nilai input tersembunyi latitude & longitude saat penanda peta digeser atau diklik.
 */
function setComplaintPoint(position) {
  const latitude = Number(position.lat);
  const longitude = Number(position.lng);

  document.getElementById("latitude").value = latitude.toFixed(7);
  document.getElementById("longitude").value = longitude.toFixed(7);
  document.getElementById("map-message").textContent =
    "Titik lokasi sudah dipilih.";

  if (!complaintMarker) {
    complaintMarker = new google.maps.Marker({
      map: complaintMap,
      position: { lat: latitude, lng: longitude },
      draggable: true,
      title: "Lokasi kejadian",
    });

    complaintMarker.addListener("dragend", (event) => {
      setComplaintPoint({
        lat: event.latLng.lat(),
        lng: event.latLng.lng(),
      });
    });
  } else {
    complaintMarker.setPosition({ lat: latitude, lng: longitude });
  }
}

/**
 * Inisialisasi peta pada halaman formulir pengaduan warga.
 */
function initComplaintMap() {
  const latitudeInput = document.getElementById("latitude");
  if (!latitudeInput) return;

  const savedLatitude = Number(latitudeInput.value);
  const savedLongitude = Number(document.getElementById("longitude").value);
  const defaultCenter = { lat: -7.3907, lng: 109.3636 }; // Titik default Purbalingga
  const center =
    savedLatitude && savedLongitude
      ? { lat: savedLatitude, lng: savedLongitude }
      : defaultCenter;

  complaintMap = new google.maps.Map(document.getElementById("complaint-map"), {
    center,
    zoom: savedLatitude ? 17 : 13,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: true,
  });

  if (savedLatitude && savedLongitude) {
    setComplaintPoint(center);
  }

  complaintMap.addListener("click", (event) => {
    setComplaintPoint({
      lat: event.latLng.lat(),
      lng: event.latLng.lng(),
    });
  });

  const locationBtn = document.getElementById("use-location");
  if (locationBtn) {
    locationBtn.addEventListener("click", () => {
      const message = document.getElementById("map-message");
      if (!navigator.geolocation) {
        message.textContent = "Perangkat ini tidak mendukung lokasi.";
        return;
      }

      message.textContent = "Mencari lokasi Anda…";
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const point = {
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          };
          complaintMap.setCenter(point);
          complaintMap.setZoom(17);
          setComplaintPoint(point);
        },
        () => {
          message.textContent =
            "Lokasi tidak tersedia. Pilih titik langsung pada peta.";
        },
        { enableHighAccuracy: true, timeout: 10000 },
      );
    });
  }
}

/**
 * Inisialisasi peta sebaran laporan pada dashboard administrator.
 */
function initAdminMap() {
  const element = document.getElementById("admin-map");
  if (!element) return;

  const reports = JSON.parse(element.dataset.reports || "[]");
  const map = new google.maps.Map(element, {
    center: { lat: -7.3907, lng: 109.3636 },
    zoom: 12,
    mapTypeControl: false,
    streetViewControl: false,
  });

  const bounds = new google.maps.LatLngBounds();
  const colors = {
    completed: "#237a46",
    processing: "#c48700",
    rejected: "#b42332",
  };

  const escapeHtml = (text) => {
    return String(text).replace(
      /[&<>'"]/g,
      (char) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          "'": "&#39;",
          '"': "&quot;",
        })[char],
    );
  };

  reports.forEach((report) => {
    const position = {
      lat: Number(report.latitude),
      lng: Number(report.longitude),
    };

    const marker = new google.maps.Marker({
      map,
      position,
      title: report.title,
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 10,
        fillColor: colors[report.status] || "#124e78",
        fillOpacity: 1,
        strokeColor: "#ffffff",
        strokeWeight: 3,
      },
    });

    const infoContent = `
      <div class="map-popup">
        <strong>${escapeHtml(report.title)}</strong>
        <span>${escapeHtml(report.ticket_number)}</span>
        <a href="admin-detail.php?ticket=${encodeURIComponent(report.ticket_number)}">Buka laporan</a>
      </div>
    `;

    const info = new google.maps.InfoWindow({ content: infoContent });
    marker.addListener("click", () => info.open({ anchor: marker, map }));
    bounds.extend(position);
  });

  if (reports.length > 0) {
    map.fitBounds(bounds, 60);
  }
}
