// Style preview for visitors (theme field "Stil-Vorschau für Besucher"). The head
// script has already set <html data-nw-palette|typeset|shape> (and data-nw-style for a
// style as starting point) from the address or the last choice and left the data in
// window.nwStylePreview; this file only drives the switcher. A whole style sets all
// three axes; colors, type and shape can then be changed on their own. The choice is
// kept in localStorage for the visit and written to the address: ?stil= for the style,
// ?farbe=, ?schrift=, ?form= for what differs from it.
(() => {
  const root = document.querySelector("[data-nw-style-preview]");
  const data = window.nwStylePreview;
  if (!root || !data) return;
  const html = document.documentElement;
  const axes = ["palette", "typeset", "shape"];
  const params = { palette: "farbe", typeset: "schrift", shape: "form" };
  const toggle = root.querySelector(".nw-style-preview__toggle");
  const panel = root.querySelector(".nw-style-preview__panel");
  const current = root.querySelector("[data-nw-style-current]");
  const currentSwatch = root.querySelector("[data-nw-style-current-swatch]");
  const backend = root.querySelector("[data-nw-style-backend]");
  const picks = [...root.querySelectorAll("[data-nw-pick]")];
  const choices = [...root.querySelectorAll("[data-nw-axis]")];
  const buttons = [...picks, ...choices];
  const key = "nw-style";

  const read = () => ({ style: html.getAttribute("data-nw-style") ?? "", ...Object.fromEntries(axes.map((axis) => [axis, html.getAttribute(`data-nw-${axis}`) ?? ""])) });
  // Axes of the state that differ from its style (all of them without a style).
  const changed = (state) => axes.filter((axis, index) => state[axis] && (!state.style || data.styles[state.style]?.[index] !== state[axis]));

  const show = (state) => {
    // Without transitions for the switch itself, else buttons fade from the old colors.
    html.classList.add("nw-style-switching");
    for (const axis of axes) {
      if (state[axis]) html.setAttribute(`data-nw-${axis}`, state[axis]);
      else html.removeAttribute(`data-nw-${axis}`);
    }
    if (state.style) html.setAttribute("data-nw-style", state.style);
    else html.removeAttribute("data-nw-style");
    void getComputedStyle(document.body).color;
    html.classList.remove("nw-style-switching");

    const own = changed(state);
    const empty = !state.style && own.length === 0;
    for (const item of picks) item.setAttribute("aria-pressed", String(item.dataset.nwPick === "" ? empty : item.dataset.nwPick === state.style && own.length === 0));
    for (const item of choices) item.setAttribute("aria-pressed", String(item.dataset.nwValue === state[item.dataset.nwAxis]));
    current.textContent = empty ? current.dataset.nwOriginal : state.style && own.length === 0 ? data.names.style[state.style] : current.dataset.nwMix;
    currentSwatch.dataset.nwSwatch = state.palette;

    // How the theme form calls it: the style, then each axis chosen on its own.
    const parts = [
      ...(state.style ? [`${backend.dataset.nwStyle} „${data.names.style[state.style]}“`] : []),
      ...own.map((axis) => `${backend.dataset[`nw${axis[0].toUpperCase()}${axis.slice(1)}`]} „${data.names[axis][state[axis]] ?? state[axis]}“`),
    ];
    backend.hidden = parts.length === 0;
    backend.textContent = `${backend.dataset.nwIntro} ${parts.join(" · ")}`;
  };

  const choose = (state) => {
    show(state);
    try {
      if (state.style || axes.some((axis) => state[axis])) localStorage.setItem(key, JSON.stringify(state));
      else localStorage.removeItem(key);
    } catch {
      // Without storage the choice lasts for this page only.
    }
    const url = new URL(location.href);
    for (const name of ["stil", ...Object.values(params)]) url.searchParams.delete(name);
    if (state.style) url.searchParams.set("stil", state.style);
    for (const axis of changed(state)) url.searchParams.set(params[axis], state[axis]);
    history.replaceState(history.state, "", url);
  };

  const open = (state) => {
    panel.hidden = !state;
    toggle.setAttribute("aria-expanded", String(state));
    if (state) {
      const pressed = buttons.find((item) => item.getAttribute("aria-pressed") === "true");
      pressed?.focus({ preventScroll: true });
    }
  };

  toggle.addEventListener("click", () => open(panel.hidden));
  for (const item of picks) {
    item.addEventListener("click", () => {
      const ids = data.styles[item.dataset.nwPick] ?? ["", "", ""];
      choose({ style: item.dataset.nwPick, ...Object.fromEntries(axes.map((axis, index) => [axis, ids[index]])) });
    });
  }
  for (const item of choices) {
    item.addEventListener("click", () => {
      const state = read();
      // "Original" of an axis goes back to the style's own, or to the theme's without a style.
      const value = item.dataset.nwValue || (state.style ? data.styles[state.style][axes.indexOf(item.dataset.nwAxis)] : "");
      choose({ ...state, [item.dataset.nwAxis]: value });
    });
  }
  panel.addEventListener("keydown", (event) => {
    const index = buttons.indexOf(document.activeElement);
    if (index < 0 || (event.key !== "ArrowDown" && event.key !== "ArrowUp")) return;
    event.preventDefault();
    buttons[(index + (event.key === "ArrowDown" ? 1 : -1) + buttons.length) % buttons.length].focus();
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !panel.hidden) {
      open(false);
      toggle.focus();
    }
  });
  document.addEventListener("click", (event) => {
    if (!panel.hidden && !root.contains(event.target)) open(false);
  });

  show(read());
  root.hidden = false;
})();
