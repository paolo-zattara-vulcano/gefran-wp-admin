jQuery(document).ready(function () {
  const { utils, i18n } = fbv_data;
  const { $message } = utils;
  var njt_auto_run_import = false;
  var fb_tools_page = new URL(fbv_data.auto_import_url);
  fb_tools_page.searchParams.delete("autorun");
  //import from old version
  jQuery(".njt_fbv_import_from_old_now").on("click", function () {
    var $this = jQuery(this);
    if ($this.hasClass("updating-message")) return false;

    $this.addClass("updating-message");

    get_folders(
      function (res) {
        if (res.success) {
          insert_folder(
            res.data.folders,
            0,
            function () {
              $this.removeClass("updating-message");
              const mess = `${i18n.filebird_db_updated} <a href="${fbv_data.media_url}">${fbv_data.i18n.go_to_media}</a>`;
              $message.success(
                (h) => h("span", { domProps: { innerHTML: mess } }),
                5
              );
              if (njt_auto_run_import) {
                location.replace(fb_tools_page.href);
              }
            },
            function () {
              $this.removeClass("updating-message");
            }
          );
        }
      },
      function () {
        $this.removeClass("updating-message");
        $message.error(
          (h) => h("span", { domProps: { innerHTML: i18n.import_failed } }),
          5
        );
      }
    );

    function get_folders(onDone, onFail) {
      jQuery
        .ajax({
          dataType: "json",
          contentType: "application/json",
          url: fbv_data.json_url + "/fb-get-old-data",
          method: "POST",
          headers: {
            "X-WP-Nonce": fbv_data.rest_nonce,
            "X-HTTP-Method-Override": "POST",
          },
        })
        .done(function (res) {
          onDone(res);
        })
        .fail(function (res) {
          onFail(res);
        });
    }
    function insert_folder(folders, index, onDone, onFail) {
      if (typeof folders[index] != "undefined") {
        jQuery
          .ajax({
            dataType: "json",
            contentType: "application/json",
            url: fbv_data.json_url + "/fb-insert-old-data",
            method: "POST",
            headers: {
              "X-WP-Nonce": fbv_data.rest_nonce,
              "X-HTTP-Method-Override": "POST",
            },
            data: JSON.stringify({
              folders: folders[index],
              autorun: njt_auto_run_import,
            }),
          })
          .done(function (res) {
            insert_folder(folders, index + 1, onDone, onFail);
          })
          .fail(function (res) {
            onFail();
            $message.error(i18n.please_try_again);
          });
      } else {
        onDone();
      }
    }
  });
  //wipe old data
  jQuery(".njt_fbv_wipe_old_data").on("click", function () {
    if (!confirm(fbv_data.i18n.are_you_sure)) return false;

    var $this = jQuery(this);

    if ($this.hasClass("updating-message")) return false;

    $this.addClass("updating-message");
    jQuery
      .ajax({
        dataType: "json",
        contentType: "application/json",
        url: fbv_data.json_url + "/fb-wipe-old-data",
        method: "POST",
        headers: {
          "X-WP-Nonce": fbv_data.rest_nonce,
          "X-HTTP-Method-Override": "POST",
        },
      })
      .done(function (res) {
        $this.removeClass("updating-message");
        $message.success(res.data.mess);
        location.reload();
      })
      .fail(function (res) {
        $this.removeClass("updating-message");
        $message.error(res.data.mess);
      });
  });
  //clear all data
  jQuery(".njt_fbv_clear_all_data").on("click", function () {
    if (!confirm(fbv_data.i18n.are_you_sure)) return false;

    var $this = jQuery(this);

    if ($this.hasClass("updating-message")) return false;

    $this.focusout().addClass("updating-message");
    jQuery
      .ajax({
        dataType: "json",
        contentType: "application/json",
        url: fbv_data.json_url + "/fb-wipe-clear-all-data",
        method: "POST",
        headers: {
          "X-WP-Nonce": fbv_data.rest_nonce,
          "X-HTTP-Method-Override": "POST",
        },
      })
      .done(function (res) {
        $this.removeClass("updating-message");
        $message.success(res.data.mess);
        location.reload();
      })
      .fail(function (res) {
        $this.removeClass("updating-message");
        $message.error(res.data.mess);
      });
  });
  //no thanks btn
  jQuery(".njt_fb_no_thanks_btn").on("click", function () {
    var $this = jQuery(this);
    $this.addClass("updating-message");
    jQuery
      .ajax({
        dataType: "json",
        contentType: "application/json",
        type: "post",
        url: fbv_data.json_url + "/fb-no-thanks",
        headers: {
          "X-WP-Nonce": fbv_data.rest_nonce,
          "X-HTTP-Method-Override": "POST",
        },
        data: JSON.stringify({
          site: $this.data("site"),
        }),
        success: function (res) {
          $this.removeClass("updating-message");
          jQuery(".njt.notice.notice-warning." + $this.data("site")).hide();
        },
      })
      .fail(function (res) {
        $this.removeClass("updating-message");
        $message.error(i18n.please_try_again);
      });
  });

  jQuery(".njt-fb-import").on("click", async function () {
    const $this = jQuery(this);
    const site = $this.data("site");

    $this.addClass("updating-message");

    try {
      await fetch(fbv_data.json_url + `/import/get-folders/${site}`, {
        headers: {
          "X-WP-Nonce": fbv_data.rest_nonce,
          "X-HTTP-Method-Override": "GET",
        },
      });

      await fetch(fbv_data.json_url + `/import/get-attachments/${site}`, {
        headers: {
          "X-WP-Nonce": fbv_data.rest_nonce,
          "X-HTTP-Method-Override": "GET",
        },
      });

      const res = await fetch(fbv_data.json_url + `/import/run/${site}`, {
        headers: {
          "X-WP-Nonce": fbv_data.rest_nonce,
          "X-HTTP-Method-Override": "GET",
        },
      });
      const { mess } = await res.json();
      $this.removeClass("updating-message");

      var html_notice =
        '<div class="njt-success-notice notice notice-success is-dismissible"><p>' +
        mess +
        '</p><button type="button" class="notice-dismiss" onClick="jQuery(\'.njt-success-notice\').remove()"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
      jQuery(html_notice).insertBefore("form#fbv-setting-form");
      $this.attr("disabled", true);
    } catch (error) {
      console.log(error);
      $this.removeClass("updating-message");
      $message.error(i18n.please_try_again);
    }
  });

  //generate API key
  jQuery(".fbv_generate_api_key_now").on("click", function () {
    if (!confirm(fbv_data.i18n.are_you_sure)) return false;
    var $this = jQuery(this);
    $this.addClass("updating-message");
    jQuery
      .ajax({
        dataType: "json",
        contentType: "application/json",
        type: "post",
        url: fbv_data.json_url + "/fbv-api",
        data: JSON.stringify({
          act: "generate-key",
        }),
        headers: {
          "X-WP-Nonce": fbv_data.rest_nonce,
          "X-HTTP-Method-Override": "POST",
        },
        success: function (res) {
          $this.removeClass("updating-message");
          if (res.success) {
            var key = res.data.key;
            jQuery("#fbv_rest_api_key").removeClass("hidden");
            jQuery("#fbv_rest_api_key").val(key);
          } else {
            $message.error(res.data.mess);
          }
        },
      })
      .fail(function (res) {
        $this.removeClass("updating-message");
        $message.error(i18n.please_try_again);
      });
  });
  //notice dismiss
  jQuery("#filebird-empty-folder-notice").on("click", function (event) {
    if (jQuery(event.target).hasClass("notice-dismiss")) {
      jQuery
        .ajax({
          dataType: "json",
          url: window.ajaxurl,
          type: "post",
          data: {
            action: "fbv_first_folder_notice",
            nonce: window.fbv_data.nonce,
          },
        })
        .done(function (result) {})
        .fail(function (res) {});
    }
  });

  function convertArrayOfObjectsToCSV(args) {
    const data = args.data;
    if (!data || !data.length) return;

    const columnDelimiter = args.columnDelimiter || ",";
    const lineDelimiter = args.lineDelimiter || "\n";

    const keys = Object.keys(data[0]);

    let result = "";
    result += keys.join(columnDelimiter);
    result += lineDelimiter;

    data.forEach((item) => {
      ctr = 0;
      keys.forEach((key) => {
        if (ctr > 0) result += columnDelimiter;
        result += item[key];
        ctr++;
      });
      result += lineDelimiter;
    });

    return result;
  }

  function generateDownloadCSV(args) {
    let csv = convertArrayOfObjectsToCSV({ data: args.data });
    if (!csv) return;

    const filename = args.filename || "export.csv";

    if (!csv.match(/^data:text\/csv/i)) {
      csv = "data:text/csv;charset=utf-8," + csv;
    }

    const data = encodeURI(csv);

    const link = document.createElement("a");
    link.setAttribute("href", data);
    link.setAttribute("download", filename);
    link.click();
  }

  jQuery(".njt-fb-csv-export").on("click", function () {
    const $this = jQuery(this);
    jQuery
      .ajax({
        dataType: "json",
        contentType: "application/json",
        type: "get",
        url: fbv_data.json_url + "/export-csv",
        headers: {
          "X-WP-Nonce": fbv_data.rest_nonce,
          "X-HTTP-Method-Override": "GET",
        },
        beforeSend: function () {
          $this.addClass("updating-message");
        },
        success: function (res) {
          jQuery("#njt-fb-download-csv")
            .unbind("click")
            .bind("click", function () {
              generateDownloadCSV({
                filename: "filebird.csv",
                data: res.folders,
              });
            });

          $this.removeClass("updating-message");
          jQuery("#njt-fb-download-csv").show();
          $message.success(i18n.successfully_exported);
        },
      })
      .fail(function (res) {
        $this.removeClass("updating-message");
        $message.error(i18n.please_try_again);
      });
  });

  jQuery("#njt-fb-upload-csv").on("change", function (event) {
    const fileValue = event.target.files;
    let fileUpload = new FormData();

    if (fileValue.length) {
      fileUpload.append("file", fileValue[0]);
      jQuery(".njt-fb-csv-import").removeClass("hidden");
      jQuery(".njt-fb-csv-import")
        .unbind("click")
        .bind("click", function () {
          $this = jQuery(this);
          jQuery
            .ajax({
              url: fbv_data.json_url + "/import-csv",
              method: "POST",
              processData: false,
              contentType: false,
              beforeSend: function () {
                $this.addClass("updating-message");
              },
              data: fileUpload,
              headers: {
                "X-WP-Nonce": fbv_data.rest_nonce,
                "X-HTTP-Method-Override": "POST",
              },
            })
            .done((res) => {
              if (res.success) {
                $this.removeClass("updating-message");
                $this.text(fbv_data.i18n.imported);
                $this.prop("disabled", true);
                $message.success(i18n.successfully_imported);
              } else {
                $this.removeClass("updating-message");

                if (res.message) {
                  $message.error(res.message);
                  return;
                }
                $message.error(i18n.please_try_again);
              }
            })
            .fail((error) => {
              console.log(error);
              $this.removeClass("updating-message");
              $message.error(i18n.please_try_again);
            });
        });
    }
  });

  jQuery(".fbv_BtnLoginEnvato").on("click", function () {
    var win = window.open(
      fbv_data.login_envato_url,
      "",
      "width=500,height=500"
    );
    var timer = setInterval(function (res) {
      if (win.closed) {
        clearInterval(timer);
        location.reload();
      }
    }, 100);
  });

  jQuery(".fbv_deactivate_license").on("click", function () {
    jQuery(".fbv_deactivate_license")
      .pointer({
        pointerClass: "wp-pointer fb-pointer",
        content: `<h3>${fbv_data.i18n.deactivate_license_confirm_title}</h3>
            <p>${fbv_data.i18n.deactivate_license_confirm_content}</p>
          `,
        position: "bottom",
        buttons: function (event, t) {
          var confirmButton = jQuery(
            '<a class="button button-primary" href="#"></a>'
          )
            .text(wp.i18n.__("Confirm"))
            .css("margin-right", "5px")
            .on("click", function (e) {
              e.preventDefault();
              e.target.classList.add("updating-message", "disabled");
              jQuery
                .ajax({
                  url: ajaxurl,
                  method: "POST",
                  data: {
                    action: "fbv_deactivate_license",
                    nonce: fbv_data.deactivate_license_nonce,
                  },
                })
                .done(function (res) {
                  e.target.classList.remove("updating-message", "disabled");
                  t.element.pointer("close");
                  location.reload();
                })
                .fail(function (res) {
                  e.target.classList.remove("updating-message", "disabled");
                  t.element.pointer("close");
                  jQuery.alert(fbv_data.i18n.deactivate_license_try_again);
                });
            });
          var closeButton = jQuery('<a class="button" href="#"></a>')
            .text(wp.i18n.__("Close"))
            .on("click", function (e) {
              e.preventDefault();
              t.element.pointer("close");
            });

          return jQuery("<div/>").append(closeButton, confirmButton);
        },
        close: function () {
          // Once the close button is hit
        },
      })
      .pointer("open");
  });

  jQuery(".fbv-tab-name").on("click", function () {
    var $this = jQuery(this);

    jQuery(".fbv-tab-name").removeClass("nav-tab-active");
    $this.addClass("nav-tab-active");

    jQuery(".fbv-tab-content").addClass("hidden");
    jQuery("#fbv-settings-tab-" + $this.attr("data-id")).removeClass("hidden");
    if (jQuery(".fb-pointer").length) {
      jQuery(".fbv_deactivate_license").pointer("close");
    }
    return false;
  });

  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const page = urlParams.get("page");
  const autorun = urlParams.get("autorun");
  if (page === "filebird-settings" && autorun == "true") {
    njt_auto_run_import = true;
    jQuery(".njt_fbv_import_from_old_now").click();
  }

  jQuery("#fbv-save-settings-submit").on("click", function (e) {
    const formData = new FormData(
      document.querySelector("form#fbv-setting-form")
    );
    const $this = this;
    jQuery
      .ajax({
        dataType: "json",
        url: window.ajaxurl,
        method: "POST",
        headers: {
          "X-WP-Nonce": fbv_data.rest_nonce,
          "X-HTTP-Method-Override": "POST",
        },
        data: {
          nonce: fbv_data.nonce,
          action: "fbv_save_settings",
          theme: formData.get("theme"),
          folderCounterType: formData.get("folderCounterType"),
          folderPerUser: !!formData.get("njt_fbv_folder_per_user"),
          showBreadCrumb: !!formData.get("showBreadCrumb"),
        },
        beforeSend: function () {
          $this.classList.add("updating-message");
        },
      })
      .done(function (res) {
        $this.classList.remove("updating-message");
        $message.success(res.data.mess);
      })
      .fail(function (res) {
        $this.classList.remove("updating-message");
        $message.error(i18n.please_try_again);
      });
  });

  jQuery(".njt_fbv_generate_attachment_size").on("click", function (e) {
    const processingStatus = jQuery(
      ".fbv-generate-attachment-size .processing-status"
    );
    const $this = this;
    const TIME_INTERVAL = 2000;
    $this.classList.add("updating-message");
    $this.textContent = fbv_data.i18n.generating;
    $this.disabled = true;
    const generating = setInterval(() => {
      jQuery
        .ajax({
          url: fbv_data.json_url + "/generate-attachment-size",
          method: "POST",
          dataType: "json",
          contentType: "application/json",
          // data: JSON.stringify({
          //   generateAll,
          // }),
          headers: {
            "X-WP-Nonce": fbv_data.rest_nonce,
            "X-HTTP-Method-Override": "POST",
          },
          beforeSend: function () {},
        })
        .done((json) => {
          if (!json.isProcessing) {
            processingStatus.hide();
            $this.classList.remove("updating-message");
            $this.disabled = false;
            $this.textContent = fbv_data.i18n.generated;
            clearInterval(generating);
            return;
          }
          if (processingStatus.css("display") === "none") {
            processingStatus.show();
            console.log("display here");
          }
          processingStatus.text(
            `${fbv_data.i18n.processing} ${json.current}/${json.total}`
          );
        })
        .fail((json) => {
          $this.classList.remove("updating-message");
          $this.disabled = false;
        });
    }, TIME_INTERVAL);
  });

  jQuery(".fbv-pro-feature").each(function (index, el) {
    jQuery("input", el).on("mousedown click", function (e) {
      e.preventDefault();
    });
    fbv_data.utils.tippy(el, {
      theme: "fbv-pro",
      content: i18n.active_to_use_feature,
      animation: "shift-away",
    });
  });
});
