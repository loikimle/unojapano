(function ($) {
  var UR_Mailchimp_Admin = {
    init: function () {
      $(".ur_mailchimp_account_action_button").on("click", function () {
        var api_key = $("#ur_mailchimp_api_key").val();
        var account_name = $("#ur_mailchimp_account_name").val();
        var form_data = new FormData();

        form_data.append("ur_mailchimp_api_key", api_key);
        form_data.append("ur_mailchimp_account_name", account_name);
        form_data.append(
          "action",
          "user_registration_mailchimp_account_action"
        );
        form_data.append(
          "security",
          ur_mailchimp_params.ur_mailchimp_account_save
        );

        $.ajax({
          url: ur_mailchimp_params.ajax_url,
          dataType: "json", // what to expect back from the PHP script, if anything
          cache: false,
          contentType: false,
          processData: false,
          data: form_data,
          type: "post",
          beforeSend: function () {
            var spinner = '<span class="ur-spinner is-active"></span>';

            $(".ur_mailchimp_account_action_button").append(spinner);
          },
          complete: function (response) {
            $(".ur_mailchimp_account_action_button")
              .find(".ur-spinner")
              .remove();

            if (response.responseJSON.success === true) {
              var $new_account = response.responseJSON.data.new_account;
              var html = "";
              html += "<tr>";
              html += "<td><strong>" + $new_account["label"] + "</strong></td>";
              html += "<td>Connected on " + $new_account["date"] + "</td>";
              html +=
                '<td><a href="#" class="disconnect ur-mailchimp-disconnect-account" data-key="' +
                $new_account["api_key"] +
                '">' +
                ur_mailchimp_params.i18n_disconnect +
                "</a></td>";
              html += "</tr>";

              if (
                $("#mailchimp_accounts table.ur-account-list-table tbody")
                  .length > 0
              ) {
                $(
                  "#mailchimp_accounts table.ur-account-list-table tbody"
                ).append(html);
              } else {
                var account_div =
                  "<div id='mailchimp_accounts' class='postbox'>";
                account_div += "<table class='ur-account-list-table'>";
                account_div += "<tbody>";
                account_div += html;
                account_div += "</tbody>";
                account_div += "</table>";
                account_div += "</div>";
                $(".mailchimp-wrapper").append(account_div);
              }
              $(".ur-mailchimp_notice").remove();
              $("#ur_mailchimp_api_key").val("");
              $("#ur_mailchimp_account_name").val("");
            } else {
              $(".ur-mailchimp_notice").remove();
              var message_string =
                '<div id="message" class="error inline ur-mailchimp_notice"><p><strong>' +
                response.responseJSON.data.message +
                "</strong></p></div>";
              $(".ur-export-users-page").prepend(message_string);
            }
          },
        });
      });

      /**
       * Remove Mailchimp Account from global settings.
       */
      $(document).on("click", ".ur-mailchimp-disconnect-account", function (e) {
        UR_Mailchimp_Admin.disconnect_account(this, e);
      });

      // Save mailchimp Integration to $_POST
      $(document).on(
        "user_registration_admin_before_form_submit",
        function (event, data) {
          var mailchimp_connections =
            UR_Mailchimp_Admin.save_mailchimp_form_settings();
          if (mailchimp_connections.length > 0) {
            data.data["ur_mailchimp_integration"] = mailchimp_connections;
          }
        }
      );

      /**
       * Add new mailchimp connection in form builder.
       */
      $(".ur-mailchimp-add-connection-button").on("click", function () {
        UR_Mailchimp_Admin.add_new_mailchimp_connection();
      });

      /**
       * Append All Data according to new API key.
       */
      $(document).on(
        "change",
        ".ur-mailchimp-settings-content-wrap #ur_mailchimp_account",
        function () {
          var $connection = $(this).closest(
            ".ur-mailchimp-settings-content-wrap"
          );
          api_key = $(this).val();
          var spinner = '<span class="ur-spinner is-active"></span>';
          $connection.find(".list-container").html("");
          $connection.find(".urmc-mailchimp-group-list").html("");
          $connection.find(".ur_mailchimp_fields tbody").html("");
          $connection.find(".list-container").append(spinner);
          UR_Mailchimp_Admin.fetchMailchimpDataByAPI(api_key, $connection);
        }
      );

      /**
       * Remove mailchimp connection from form builder.
       */
      $(document).on("click", ".user-registration-card__remove", function () {
        $connection = $(this).closest(".ur-mailchimp-settings-content-wrap");
        ur_confirmation(
          user_registration_form_builder_data.i18n_admin
            .i18n_are_you_sure_want_to_delete_row,
          {
            title:
              user_registration_form_builder_data.i18n_admin.i18n_msg_delete,
            confirm: function () {
              $connection.remove();

              Swal.fire({
                icon: "success",
                title: "Successfully deleted!",
                customClass:
                  "user-registration-swal2-modal user-registration-swal2-modal--center",
                showConfirmButton: false,
                timer: 1000,
              });
            },
            reject: function () {
              // Do Nothing.
            },
          }
        );
      });

      /**
       * Append mailchimp groups and list field according to list id.
       */
      $(document).on(
        "change",
        ".ur-mailchimp-settings-content-wrap #ur_mailchimp_integration_list_id",
        function () {
          var $connection = $(this).closest(
            ".ur-mailchimp-settings-content-wrap"
          );
          list_id = $(this).val();
          var connection_id = $connection.data("connection-id");
          var mailchimp_list = JSON.parse(
            localStorage.getItem("mailchimp_list_" + connection_id)
          );
          var spinner = '<span class="ur-spinner is-active"></span>';
          $connection.find(".urmc-mapping-label").after(spinner);

          $connection.find(".urmc-mailchimp-group-list").html("");
          $connection.find(".ur_mailchimp_fields").html("");

          UR_Mailchimp_Admin.appendMailchimpGroups(
            list_id,
            mailchimp_list,
            $connection
          );
          UR_Mailchimp_Admin.appendMailchimpFields(
            list_id,
            mailchimp_list,
            $connection
          );
          $connection.find(".ur-spinner").remove();
        }
      );
    },
    /**
     * Add new Mailchimp Connection in Global settings.
     */
    add_new_mailchimp_connection: function () {
      swal.fire({
        icon: "info",
        title: ur_mailchimp_params.i18n_new_connection_title,
        html: ur_mailchimp_params.i18n_new_connection_html,
        confirmButtonText: ur_mailchimp_params.i18n_new_connection_button_text,
        confirmButtonColor: "#3085d6",
        showConfirmButton: true,
        showCancelButton: true,
        cancelButtonText: ur_mailchimp_params.i18n_cancel,
        customClass: {
          container: "user-registration-swal2-container",
        },
        focusConfirm: false,
        showLoaderOnConfirm: true,
        preConfirm: function () {
          return new Promise(function (resolve) {
            var ur_mailchimp_new_connection_name =
              Swal.getPopup().querySelector(
                "#ur_mailchimp_new_connection_name"
              ).value;

            if (!ur_mailchimp_new_connection_name) {
              Swal.showValidationMessage(
                ur_mailchimp_params.i18n_please_enter_connection_name
              );

              Swal.hideLoading();

              $(".swal2-actions").find("button").prop("disabled", false);
            } else {
              UR_Mailchimp_Admin.load_new_mailchimp_connection(
                ur_mailchimp_new_connection_name
              );
              Swal.close();
            }
          });
        },
      });
    },
    /**
     * Save Mailchimp Form Settings from form builder
     */
    save_mailchimp_form_settings: function () {
      var mailchimp_connections = new Array();
      var connection = $(".ur-mailchimp-settings").find(
        ".ur-mailchimp-settings-content-wrap "
      );
      $.each(connection, function (key, value) {
        var connection_name = $(this)
          .find(".user-registration-card__title")
          .html();
        var connection_id = $(this).data("connection-id");
        var $api_key = $(this).find("#ur_mailchimp_account").val();

        var list_id = $(this).find("#ur_mailchimp_integration_list_id").val();
        var double_optin = $(this)
          .find("#ur_mailchimp_double_optin")
          .is(":checked");
        var fields = $(this).find(".ur_mailchimp_fields table select");
        var list_fields = {};
        $.each(fields, function (key, field) {
          list_fields[field.id] = field.value;
        });

        var form_fields = $(this).find(
          ".ur_conditional_logic_container .ur-conditional-wrapper"
        );
        var enable_conditional_logic = $(this)
          .find("#ur_use_conditional_logic")
          .val();
        var enable_conditional_logic = $(this)
          .find("#ur_use_conditional_logic")
          .is(":checked");
        var conditional_logic_data = {};
        $.each(form_fields, function (key, field) {
          conditional_logic_data["conditional_field"] = $(this)
            .find(".ur_conditional_field")
            .val();
          conditional_logic_data["conditional_operator"] = $(this)
            .find(".ur-conditional-condition")
            .val();
          conditional_logic_data["conditional_value"] = $(this)
            .find(".ur-conditional-input")
            .val();
        });
        /**
         * Previously Data is saved according to input type
         * To make backward compatibility implemting as previous approach.
         */

        var list_group = {};
        var groups = $(this).find(".ur-mailchimp-group-type");
        var interest_group_checkbox = new Array();
        var interest_group_radio = new Array();
        var interest_group_dropdown = new Array();

        $.each(groups, function (key, group) {
          var interest_type = group.dataset.interests_type;

          if ($(this).find("input:checkbox").length > 0) {
            $(this)
              .find("input:checkbox:checked")
              .each(function () {
                interest_group_checkbox.push($(this).val());
              });
            list_group[interest_type] = interest_group_checkbox;
          }

          if ($(this).find("input:radio").length > 0) {
            $(this)
              .find("input:radio:checked")
              .each(function () {
                interest_group_radio.push($(this).val());
              });
            list_group[interest_type] = interest_group_radio;
          }

          if ($(this).find("select").length > 0) {
            interest_group_dropdown.push($(this).find("select").val());
            list_group[interest_type] = interest_group_dropdown;
          }
        });

        var integration = {
          connection_id: connection_id,
          name: connection_name,
          api_key: $api_key,
          list_id: list_id,
          list_fields: JSON.stringify(list_fields),
          list_group: JSON.stringify(list_group),
          double_optin: double_optin,
          enable_conditional_logic: enable_conditional_logic,
          conditional_logic_data: conditional_logic_data,
        };
        mailchimp_connections.push(integration);
      });
      return mailchimp_connections;
    },
    /**
     * Append new accordian according to connection name.
     * @param {string} new_connection_name  Connection name.
     */
    load_new_mailchimp_connection: function (new_connection_name) {
      var connection_id = "connection_" + Date.now();
      var html = "";

      html +=
        '<div class="user-registration-card ur-mb-2 ur-mailchimp-settings-content-wrap" data-connection-id="' +
        connection_id +
        '">';
      html +=
        '<div class="user-registration-card__header ur-d-flex ur-align-items-center ur-p-3">';
      html +=
        '<h4 class="user-registration-card__title">' +
        new_connection_name +
        "</h4>";
      html += '<div class="user-registration-card__button">';
      html +=
        '<button class="user-registration-card__toggle button button-secondary button-icon">';
      html +=
        '<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><polyline points="6 9 12 15 18 9"></polyline></svg>';
      html += "</button>";
      html +=
        '<button class="user-registration-card__remove button button-secondary button-icon">';
      html +=
        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>';
      html += "</button>";
      html += "</div>";
      html += "</div>";
      html +=
        '<div class="user-registration-card__body ur-p-3" style="display:none;">';
      var account_list = Object.values(
        ur_mailchimp_params.ur_mailchimp_account_lists
      );
      if (account_list.length > 0) {
        var api_key = account_list[0]["api_key"];
        html += '<p class="">Select Account </p>';
        html += '<select id="ur_mailchimp_account" class="">';
        $.each(account_list, function (key, account) {
          html +=
            '<option value="' +
            account["api_key"] +
            '">' +
            account["label"] +
            "</option>";
        });
        html += "</select>";
        html += '<div class="">';
        html += '<div class="urmc-mailchimp-list-wrap">';
        html += "<p>Select List</p>";
        html += '<div class="list-container">';
        html += '<span class="ur-spinner is-active"></span>';
        html += "</div>";
        html += "</div>";
        html += '<div class="urmc-mailchimp-group-wrap">';
        html += '<div class="urmc-mailchimp-group-list">';
        html += "</div>";
        html += "</div>";
        html += '<div class="ur_mailchimp_fields">';
        html += "</div>";
        html += '<div class="ur_mailchimp_options_container">';
        html += "<h4> Options </h4>";

        html += '<div class="ur_mailchimp_double_optin_container">';
        html += '<div class="ur_mailchimp_double_optin ur-check">';
        html +=
          '<input class="ur-enable-double-optin" id="ur_mailchimp_double_optin" type="checkbox" name="ur_mailchimp_double_optin">';
        html +=
          '<label for="ur_mailchimp_double_optin">Use double optin</label>';

        html += "</div>";
        html += "</div>";
        html += '<div class="ur_conditional_logic_container">';
        html += '<div class="ur_use_conditional_logic_wrapper ur-check">';
        html +=
          '<input class="ur-use-conditional-logic" type="checkbox" id="ur_use_conditional_logic" name="ur_use_conditional_logic">';
        html += "<label>Use conditional logic</label>";

        html += "</div>";
        html +=
          '<div class="ur_conditional_logic_wrapper" data-source="mailchimp" style="display: none;">';
        html += "<h4>Conditional Rules</h4>";
        html +=
          '<div class="ur-logic"><p>Send data only if the following matches.</p></div>';
        html += '<div class="ur-conditional-wrapper">';
        var fields = [];
        $(".ur-selected-item").each(function () {
          field_type = $(this).find(".ur-field").data("field-key");
          field_label = $(this)
            .find(".ur-general-setting-label .ur-general-setting-field")
            .val();
          field_id = $(this)
            .find(".ur-general-setting-field-name .ur-general-setting-field")
            .val();
          var selectedAttr = "";
          var selectedOption = "";

          if (field_type && field_label && field_id) {
            if (selectedOption === field_id) {
              selectedAttr = 'selected="selected"';
            }

            fields.push(
              '<option data-type="' +
                field_type +
                '" data-label="' +
                field_label +
                '" value="' +
                field_id +
                '" ' +
                selectedAttr +
                ">" +
                field_label +
                "</option>"
            );
          }
        });

        html +=
          '<select class="ur_conditional_field" name="ur_conditional_field">' +
          fields +
          "</select>";
        html +=
          '<select class="ur-conditional-condition" name="ur-conditional-condition">';
        html += '<option value="is"> is </option>';
        html += '<option value="is_not"> is not </option>';
        html += "</select>";
        html +=
          '<input class="ur-conditional-input" type="text" name="ur-conditional-input" value="">';

        html += "</div>";
        html += "</div>";
        html += "</div>";
        html += "</div>";
      }
      html += "</div>";
      html += "</div>";
      html += "</div>";
      $(".ur-mailchimp_settings-container")
        .find(".ur-mailchimp-settings")
        .append(html);

      var $connection = $('[data-connection-id="' + connection_id + '"]');
      UR_Mailchimp_Admin.fetchMailchimpDataByAPI(api_key, $connection);
    },
    /**
     * Get Mailchimp Lists according to api key.
     * @param {string} api_key
     * @returns
     */
    get_mailchimp_lists_by_api_key: function (api_key) {
      var form_data = new FormData();
      form_data.append("ur_mailchimp_api_key", api_key);
      form_data.append(
        "action",
        "user_registration_mailchimp_lists_by_api_key_action"
      );
      if ("" !== form_data) {
        html = $.ajax({
          url: ur_mailchimp_params.ajax_url,
          dataType: "json", // what to expect back from the PHP script, if anything
          cache: false,
          contentType: false,
          processData: false,
          data: form_data,
          type: "post",
        });
        return html;
      }
    },
    /**
     * Remove mailchimp account from global settings.
     */
    disconnect_account: function (el, e) {
      e.preventDefault();
      var $this = $(el);
      data = {
        action: "user_registration_mailchimp_account_disconnect_action",
        security: ur_mailchimp_params.ur_mailchimp_account_disconnect,
        api_key: $this.data("key"),
      };

      ur_confirmation(
        user_registration_form_builder_data.i18n_admin
          .i18n_are_you_sure_want_to_delete_row,
        {
          title: user_registration_form_builder_data.i18n_admin.i18n_msg_delete,
          confirm: function () {
            $.ajax({
              url: ur_mailchimp_params.ajax_url,
              method: "POST",
              data: data,
            }).done(function (response) {
              if (response.success === true) {
                Swal.fire({
                  icon: "success",
                  title: response.data.message,
                  customClass:
                    "user-registration-swal2-modal user-registration-swal2-modal--center",
                  showConfirmButton: false,
                  timer: 1000,
                }).then(function (result) {
                  window.location.reload();
                });
              } else {
                Swal.showValidationMessage(response.data.message);
                $(".swal2-actions").removeClass("swal2-loading");
                $(".swal2-actions").find("button").prop("disabled", false);
              }
            });
          },
          reject: function () {
            // Do Nothing.
          },
        }
      );
    },
    /**
     * Fetch mailchimp Data by API Key.
     * @param {string} api_key
     * @param {*} $connection
     */
    fetchMailchimpDataByAPI: function (api_key, $connection) {
      $connection.find(".urmc-mailchimp-group-wrap").hide();
      $connection.find(".ur_mailchimp_fields").hide();
      UR_Mailchimp_Admin.get_mailchimp_lists_by_api_key(api_key).then(function (
        response
      ) {
        if (!response.success) {
          return;
        }
        var connection_id = $connection.data("connection-id");
        localStorage.setItem(
          "mailchimp_list_" + connection_id,
          JSON.stringify(response.data.mailchimp_list)
        );

        UR_Mailchimp_Admin.appendMailchimpList(
          response.data.mailchimp_list,
          $connection
        );
      });
    },
    /**
     * Append Mailchimp List According to API Key.
     */
    appendMailchimpList: function (mailchimp_list, $connection) {
      /**
       * List integrations.
       */
      var options = "<select id='ur_mailchimp_integration_list_id'>";

      $.each(mailchimp_list, function (key, account) {
        options +=
          '<option value="' +
          account.list_id +
          '">' +
          account.list_title +
          "</option>";
      });
      options += "</select>";

      $connection.find(".ur-spinner").remove();
      $connection.find(".list-container").append(options);

      $connection.find(".urmc-mailchimp-group-list").html("");
      $connection.find(".ur_mailchimp_fields").html("");

      var selected_list_id = mailchimp_list[0]["list_id"];

      UR_Mailchimp_Admin.appendMailchimpGroups(
        selected_list_id,
        mailchimp_list,
        $connection
      );
      UR_Mailchimp_Admin.appendMailchimpFields(
        selected_list_id,
        mailchimp_list,
        $connection
      );
    },
    /**
     * Append Groups according to List ID.
     */
    appendMailchimpGroups: function (
      selected_list_id,
      mailchimp_list,
      $connection
    ) {
      /**
       * Prepare second group.
       */
      $.each(mailchimp_list, function (key, account) {
        if (selected_list_id === account.list_id) {
          var list_fields = JSON.parse(account.list_fields);
          var interests = list_fields["interests"];
          var group_html = "";
          $.each(interests, function (key, interest_group) {
            group_html +=
              '<div class="ur-mailchimp-group-type urmc-mailchimp-' +
              interest_group.type +
              '" data-id="' +
              interest_group.id +
              '" data-interests_type="' +
              interest_group.type +
              '">';
            group_html +=
              '<label class="ur-mailchimp-group-title">' +
              interest_group.title +
              "</label>";
            if (
              "checkboxes" === interest_group.type ||
              "hidden" === interest_group.type
            ) {
              $.each(interest_group.groups, function (key, group) {
                group_html += '<div class="ur-check">';
                group_html +=
                  '<input type="checkbox" name="urmc_mailchimp_settings_' +
                  interest_group.id +
                  '_checkboxes[]" id="urmc_mailchimp_settings_' +
                  group.id +
                  '" value="' +
                  group.id +
                  '">';
                group_html +=
                  '<label for="urmc_mailchimp_settings_' +
                  group.id +
                  '">' +
                  group.name +
                  "</label>";
                group_html += "</div>";
              });
            }
            if ("radio" === interest_group.type) {
              $.each(interest_group.groups, function (key, group) {
                group_html += '<div class="ur-check">';
                group_html +=
                  '<input type="radio" name="urmc_mailchimp_settings_' +
                  interest_group.id +
                  '_radio[]" id="urmc_mailchimp_settings_' +
                  group.id +
                  '" value="' +
                  group.id +
                  '">';
                group_html +=
                  '<label for="urmc_mailchimp_settings_' +
                  group.id +
                  '">' +
                  group.name +
                  "</label>";
                group_html += "</div>";
              });
            }
            if ("dropdown" === interest_group.type) {
              group_html +=
                '<select name="urmc_mailchimp_settings_' +
                interest_group.id +
                '_dropdown" id="urmc_mailchimp_settings_' +
                interest_group.id +
                '_dropdown">';

              $.each(interest_group.groups, function (key, group) {
                group_html +=
                  '<option value="' +
                  group.id +
                  '">' +
                  group.name +
                  "</option>";
              });
              group_html += "</select> ";
            }
            group_html += "</div>";
          });
          if (group_html !== "") {
            var html =
              '<label class="urmc-mapping-label">Select Groups</label>' +
              group_html;
            $connection.find(".urmc-mailchimp-group-list").append(html);
            $connection.find(".urmc-mailchimp-group-wrap").show();
          }
        }
      });
    },
    /**
     * Append Mailchimp Fields according to List id.
     */
    appendMailchimpFields: function (
      selected_list_id,
      mailchimp_list,
      $connection
    ) {
      $.each(mailchimp_list, function (key, account) {
        if (selected_list_id === account.list_id) {
          var list_fields = JSON.parse(account.list_fields);
          var field_html = "";
          $.each(list_fields, function (key, mailchimp_fields) {
            var selectedOption = "";
            if ("interests" !== key) {
              field_html += "<tr>";

              field_html +=
                '<td class="column-lists">' + mailchimp_fields.name + "</td>";
              field_html += '<td class="column-form-fields">';
              field_html += UR_Mailchimp_Admin.getFormFields(
                selectedOption,
                mailchimp_fields
              );
              field_html += "</td>";
              field_html += "</tr>";
            }
          });
          if (field_html !== "") {
            var mailchimp_fields =
              '<p>Map Fields</p><table class="wp-list-table widefat striped list-fields">';
            mailchimp_fields +=
              '<thead><tr><th scope="col" class="column-lists">List Fields</th><th scope="col" class="column-form-fields">Available Form Fields</th></tr></thead>';
            mailchimp_fields += "<tbody>" + field_html + "</tbody></table>";
            $connection.find(".ur_mailchimp_fields").append(mailchimp_fields);
            $connection.find(".ur_mailchimp_fields").show();
          }
        }
      });
    },
    /**
     * Get a list of fields wrapped in a "select" tag.
     *
     * @param {String} selectedOption Default selected option ID.
     * @param {Array<String>} exclude_ids IDs to exclude.
     * @param {Array<String>} exclude_field_types Field types to exclude.
     *
     * @returns {String}
     */
    getFormFields: function (
      selectedOption,
      mailchimp_fields,
      exclude_field_types
    ) {
      var fields = ['<option value="">Ignore this field</option>'],
        field_type,
        field_label,
        field_id,
        selectedAttr;

      if (!Array.isArray(exclude_field_types)) {
        exclude_field_types = [
          "file",
          "profile_picture",
          "single_item",
          "section_title",
          "html",
          "wysiwyg",
          "billing_address_title",
          "shipping_address_title",
        ];
      }

      $(".ur-selected-item").each(function () {
        field_type = $(this).find(".ur-field").data("field-key");
        field_label = $(this)
          .find(".ur-general-setting-label .ur-general-setting-field")
          .val();
        field_id = $(this)
          .find(".ur-general-setting-field-name .ur-general-setting-field")
          .val();
        selectedAttr = "";
        disabledAttr = "";

        if (field_type && field_label && field_id) {
          if (!exclude_field_types.includes(field_type)) {
            if ("email_address" === mailchimp_fields.tag) {
              selectedOption = "user_email";
              disabledAttr = "disabled";
            }
            if (selectedOption === field_id) {
              selectedAttr = 'selected="selected"';
            }

            fields.push(
              '<option data-type="' +
                field_type +
                '" data-label="' +
                field_label +
                '" value="' +
                field_id +
                '" ' +
                selectedAttr +
                ">" +
                field_label +
                "</option>"
            );
          }
        }
      });

      return (
        '<select id="' +
        mailchimp_fields.tag +
        '" ' +
        disabledAttr +
        ">" +
        fields.join("") +
        "</select>"
      );
    },
  };
  $(document).ready(function () {
    UR_Mailchimp_Admin.init();
  });
})(jQuery);
