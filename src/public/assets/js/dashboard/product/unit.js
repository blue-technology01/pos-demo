$(function () {
  // ─── Constants ────────────────────────────────────────────────────────────
  const SEL = {
    dialog:  "#pm-dialog",
    form:    "#pm-form",
    openBtn: "#open-pm-modal",
    // Form fields
    id:           "#pm_id",
    name:         "#pm_name",
    code:         "#pm_code",
    allowDecimal: "#pm_allow_decimal",
    status:       "#pm_status",
  };

  // ─── Dialog init ──────────────────────────────────────────────────────────
  const $dialog = $(SEL.dialog).dialog({
    autoOpen:  false,
    modal:     true,
    width:     400,
    resizable: false,
    draggable: false,
    open() {
      $(this)
        .closest(".ui-dialog")
        .find(".ui-dialog-titlebar-close")
        .html("&times;");
    },
    buttons: {
      "Save Unit"() { $(SEL.form).submit(); },
      Cancel()      { $(this).dialog("close"); },
    },
  });

  // modal logic
  function openUnitModal($row = null) {
    $(SEL.form)[0].reset();

    if ($row?.length) {
      populateForm($row);
    } else {
      $(SEL.id).val("");
      $dialog.dialog("option", "title", "Add New Unit");
    }
    $dialog.dialog("open");
  }

  function populateForm($row) {
    const unitId = $row.data("unit-id");

    $(SEL.id).val(unitId);
    $(SEL.name).val($row.find(".unit-name-text").text().trim());
    $(SEL.code).val($row.find(".unit-badge").text().trim());
    $(SEL.allowDecimal).val($row.find("[data-allow-decimal]").data("allow-decimal"));
    $(SEL.status).val($row.find("[data-status]").data("status"));

    $dialog.dialog("option", "title", `Edit Unit #${unitId}`);
  }

  function deleteUnit($row) {
    const unitId = $row.data("unit-id");
    if (!confirm(`Are you sure you want to delete unit #${unitId}?`)) return;

    console.log("Processing AJAX deletion for ID:", unitId);
    // Add your AJAX delete request logic here
  }

  // ─── Action dispatch ──────────────────────────────────────────────────────
  const ACTIONS = {
    edit:   ($row) => openUnitModal($row),
    delete: ($row) => deleteUnit($row),
  };

  // ─── Events ───────────────────────────────────────────────────────────────
  $(SEL.openBtn).on("click", (e) => {
    e.preventDefault();
    openUnitModal();
  });

  $(document).on("click", ".btn-action", function (e) {
    e.preventDefault();
    const action = $(this).data("action");
    const handler = ACTIONS[action];

    if (handler) {
      handler($(this).closest("tr"));
    } else {
      console.warn("Unmapped action:", action);
    }
  });
});
