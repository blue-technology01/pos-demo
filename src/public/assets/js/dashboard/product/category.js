$(function () {
  //constansts
  const ROUTES = {
    store:  "/dashboard/categories/store",
    update: (id) => `/dashboard/categories/${id}/update`,
  };

  const SEL = {
    dialog:    "#pm-dialog",
    form:      "#pm-form",
    openBtn:   "#open-pm-modal",
    tableBody: "#unit-table-body",
    editBtn:   ".btn-action[data-action='edit']",
    // Form fields
    name:      "#pm_name",
    code:      "#pm_code",
    status:    "#pm_status",
  };

  // dialog init
  const $dialog = $(SEL.dialog).dialog({
    autoOpen:  false,
    modal:     true,
    width:     450,
    resizable: false,
    draggable: false,
    open() {
      $(this)
        .closest(".ui-dialog")
        .find(".ui-dialog-titlebar-close")
        .html("&times;");
    },
    buttons: {
      "Save Category"() { $(SEL.form).submit(); },
      Cancel()          { $(this).dialog("close"); },
    },
  });

  // events
  $(SEL.openBtn).on("click", () => openModal());

  $(SEL.tableBody).on("click", SEL.editBtn, function () {
    openModal($(this).closest("tr"));
  });

  // modal logic
  function openModal($row = null) {
    $(SEL.form)[0].reset();

    if ($row?.length) {
      populateForm($row);
    } else {
      $dialog.dialog("option", "title", "Add New Category");
      $(SEL.form).attr("action", ROUTES.store);
    }

    $dialog.dialog("open");
  }

  function populateForm($row) {
    const unitId      = $row.data("unit-id");
    const catName     = $row.find(".unit-name-text").text().trim();
    const description = $row.find(".unit-badge").text().trim();
    const status      = $row.find(".status-badge").text().trim().toLowerCase();

    $dialog.dialog("option", "title", "Edit Category");

    $(SEL.name).val(catName);
    $(SEL.code).val(description);
    $(SEL.status).val(status);
    $(SEL.form).attr("action", ROUTES.update(unitId));
  }
});
