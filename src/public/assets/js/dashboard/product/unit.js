$(function () {

  const SEL = {
    dialog:  "#pm-dialog",
    form:    "#pm-form",
    openBtn: "#open-pm-modal",
    id:           "#pm_id",
    name:         "#pm_name",
    code:         "#pm_code",
    allowDecimal: "#pm_allow_decimal",
    status:       "#pm_status",
  };

  // Using jQuery UI Dialog for simplicity
  const $dialog = $(SEL.dialog).dialog({
    autoOpen:  false,
    modal:     true,
    width:     400,
    resizable: false,
    draggable: false,

    // Customize close button
    open() {
      $(this)
        .closest(".ui-dialog")
        .find(".ui-dialog-titlebar-close")
        .html("&times;");
    },
    // Form submission logic
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
});
