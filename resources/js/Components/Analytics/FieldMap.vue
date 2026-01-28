<template>
  <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center">
      <h3 class="font-bold text-gray-800 flex items-center gap-2">
        <span class="text-xl">🗺️</span> Field Status Map
      </h3>
      <div class="flex gap-3 text-xs">
        <div class="flex items-center gap-1">
          <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Active
        </div>
        <div class="flex items-center gap-1">
          <span class="w-3 h-3 rounded-full bg-red-500"></span> Pest Concern
        </div>
        <div class="flex items-center gap-1">
          <span class="w-3 h-3 rounded-full bg-gray-400"></span> Idle
        </div>
      </div>
    </div>
    
    <div id="field-analytics-map" class="h-96 w-full z-0 relative"></div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, watch } from 'vue';

const props = defineProps({
  fields: {
    type: Array,
    default: () => [],
  },
});

let map = null;

const loadLeaflet = () => {
  return new Promise((resolve, reject) => {
    if (window.L) {
      resolve(window.L);
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = () => resolve(window.L);
    script.onerror = reject;
    document.head.appendChild(script);

    const link = document.createElement('link');
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    link.rel = 'stylesheet';
    document.head.appendChild(link);
  });
};

const initMap = async () => {
  try {
    const L = await loadLeaflet();
    
    // Default center (Bukidnon)
    const defaultCenter = [8.157, 125.024]; 
    
    // Cleanup existing map
    if (map) {
      map.remove();
      map = null;
    }

    // Initialize map
    map = L.map('field-analytics-map').setView(defaultCenter, 10);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    renderFields(L);
  } catch (error) {
    console.error('Failed to load map:', error);
  }
};

const renderFields = (L) => {
  if (!map || !props.fields.length) return;

  const bounds = L.latLngBounds();
  let hasValidLayers = false;

  props.fields.forEach(field => {
    let color = '#9ca3af'; // Gray (Idle)
    if (field.has_pests) color = '#ef4444'; // Red (Pest)
    else if (field.status === 'active') color = '#10b981'; // Green (Active)

    // Handle Polygon Coordinates
    if (field.coordinates && Array.isArray(field.coordinates) && field.coordinates.length > 0) {
      // Ensure coordinates are [lat, lng]
      const polygon = L.polygon(field.coordinates, {
        color: color,
        fillColor: color,
        fillOpacity: 0.4,
        weight: 2
      }).addTo(map);

      polygon.bindPopup(`
        <div class="font-sans">
          <h4 class="font-bold text-gray-800">${field.name}</h4>
          <div class="text-xs text-gray-600 mt-1">Size: ${field.size} ha</div>
          <div class="text-xs font-medium mt-1 capitalize" style="color: ${color}">
            ${field.has_pests ? 'Pest Risk Detected' : field.status}
          </div>
        </div>
      `);
      
      bounds.extend(polygon.getBounds());
      hasValidLayers = true;
    } 
    // Handle Point Location
    else if (field.location && field.location.lat && field.location.lng) {
      const marker = L.circleMarker([field.location.lat, field.location.lng], {
        radius: 8,
        fillColor: color,
        color: '#fff',
        weight: 2,
        opacity: 1,
        fillOpacity: 0.8
      }).addTo(map);

      marker.bindPopup(`
        <div class="font-sans">
          <h4 class="font-bold text-gray-800">${field.name}</h4>
          <div class="text-xs text-gray-600 mt-1">Size: ${field.size} ha</div>
          <div class="text-xs font-medium mt-1 capitalize" style="color: ${color}">
            ${field.has_pests ? 'Pest Risk Detected' : field.status}
          </div>
        </div>
      `);

      bounds.extend([field.location.lat, field.location.lng]);
      hasValidLayers = true;
    }
  });

  if (hasValidLayers) {
    map.fitBounds(bounds, { padding: [50, 50] });
  }
};

onMounted(() => {
  initMap();
});

watch(() => props.fields, () => {
    if(window.L && map) {
        // Clear layers logic could be here but for simplicity we re-init
        // A better approach would be layerGroup but let's just re-render layers
        // Actually simplest is re-calling renderFields but we need to clear previous layers.
        // For MVP, re-init is fine or just keeping it static if data doesn't change often.
        // Let's rely on initMap clearing it.
        initMap();
    }
}, { deep: true });

onBeforeUnmount(() => {
  if (map) {
    map.remove();
  }
});
</script>

<style scoped>
/* Fix Leaflet z-index issues if any */
#field-analytics-map {
  z-index: 1;
}
</style>
