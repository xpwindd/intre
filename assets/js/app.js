async function postForm(url, formData) {
  const response = await fetch(url, { method: "POST", body: formData });
  return response.json();
}

function setButtonBusy(button, isBusy) {
  if (!button) return;
  if (isBusy) {
    button.dataset.originalText = button.textContent || "";
    button.disabled = true;
    button.textContent = "Подождите...";
    return;
  }
  button.disabled = false;
  if (button.dataset.originalText) {
    button.textContent = button.dataset.originalText;
    delete button.dataset.originalText;
  }
}

function bindDeviceAjax() {
  document.querySelectorAll("[data-device-toggle]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const fd = new FormData();
      fd.append("id", btn.dataset.id);
      fd.append("_csrf", btn.dataset.csrf);

      try {
        setButtonBusy(btn, true);
        const data = await postForm("/?route=api/devices/toggle", fd);
        if (!data.ok) return;

        btn.textContent = data.status === "on" ? "Выключить" : "Включить";
        btn.dataset.originalText = btn.textContent;

        const badge = document.querySelector(`#device-status-${btn.dataset.id}`);
        if (!badge) return;

        badge.textContent = data.status === "on" ? "Включено" : "Выключено";
        badge.className = `status ${data.status === "on" ? "status-ok" : "status-warn"}`;
      } catch (error) {
        // Ignore temporary API errors, keep current UI state.
      } finally {
        setButtonBusy(btn, false);
      }
    });
  });
}

function bindNotificationsAjax() {
  document.querySelectorAll("[data-notification-read]").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const fd = new FormData();
      fd.append("id", btn.dataset.id);
      fd.append("_csrf", btn.dataset.csrf);

      try {
        setButtonBusy(btn, true);
        const data = await postForm("/?route=api/notifications/read", fd);
        if (!data.ok) return;

        const statusCell = btn.closest("td");
        if (statusCell) {
          statusCell.innerHTML = '<span class="status status-ok">Прочитано</span>';
        }
        btn.closest("tr")?.classList.add("muted");
      } catch (error) {
        // Ignore temporary API errors, keep current UI state.
      } finally {
        setButtonBusy(btn, false);
      }
    });
  });
}

function statusLabel(value, min, max) {
  if (value < min) return "Низкая";
  if (value > max) return "Высокая";
  return "Норма";
}

function statusClass(value, min, max) {
  if (value < min) return "status-warn";
  if (value > max) return "status-danger";
  return "status-ok";
}

async function refreshSensors() {
  const holder = document.querySelector("[data-sensors-live]");
  if (!holder) return;

  try {
    const res = await fetch("/?route=api/sensors");
    const data = await res.json();
    if (!data.ok || !Array.isArray(data.data) || data.data.length === 0) {
      holder.textContent = "Онлайн-данных пока нет.";
      return;
    }

    const last = data.data[data.data.length - 1];
    const soil = Number(last.soil_humidity || 0);
    const temp = Number(last.temperature || 0);
    const air = Number(last.air_humidity || 0);
    const light = Number(last.light_level || 0);

    holder.innerHTML = `<strong>Онлайн:</strong> Влажность почвы ${soil.toFixed(1)}%, Температура ${temp.toFixed(1)}°C, Влажность воздуха ${air.toFixed(1)}%, Освещенность ${light.toFixed(0)} lx`;

    const soilValue = document.querySelector("[data-live-soil]");
    const tempValue = document.querySelector("[data-live-temp]");
    const lightValue = document.querySelector("[data-live-light]");
    const soilBadge = document.querySelector("[data-live-soil-status]");
    const tempBadge = document.querySelector("[data-live-temp-status]");
    const lightBadge = document.querySelector("[data-live-light-status]");

    if (soilValue) soilValue.textContent = `${soil.toFixed(1)}%`;
    if (tempValue) tempValue.textContent = `${temp.toFixed(1)}°C`;
    if (lightValue) lightValue.textContent = `${light.toFixed(0)} lx`;

    if (soilBadge) {
      soilBadge.textContent = statusLabel(soil, 45, 75);
      soilBadge.className = `status ${statusClass(soil, 45, 75)}`;
    }
    if (tempBadge) {
      tempBadge.textContent = statusLabel(temp, 18, 30);
      tempBadge.className = `status ${statusClass(temp, 18, 30)}`;
    }
    if (lightBadge) {
      lightBadge.textContent = statusLabel(light, 2500, 11000);
      lightBadge.className = `status ${statusClass(light, 2500, 11000)}`;
    }
  } catch (error) {
    holder.textContent = "Не удалось обновить онлайн-данные.";
  }
}

document.addEventListener("DOMContentLoaded", () => {
  bindDeviceAjax();
  bindNotificationsAjax();
  refreshSensors();
  if (document.querySelector("[data-sensors-live]")) {
    setInterval(refreshSensors, 15000);
  }
});
