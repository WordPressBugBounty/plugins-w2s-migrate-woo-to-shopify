(function ($) {
	'use strict';

	$(document).ready(function () {

		$(document).on('click', '.viw2s-remove-new-row', function (e) {
			e.preventDefault();
			$(this).closest('tr').remove();

			if ($('.viw2s-unified-api-table tbody tr').length === 0) {
				$('.viw2s-unified-api-table tbody').html(
					'<tr class="viw2s-no-connections">' +
					'<td colspan="5">No API connection configured yet. Click "Add Connection" to get started.</td>' +
					'</tr>'
				);
			}
		});

		$(document).on('change', '.viw2s-new-api-type', function () {
			var isOAuth = $(this).val() === 'oauth';
			var $row = $(this).closest('tr');
			var config = {
				cred1: isOAuth ? 'Client ID' : 'API Key',
				cred2: isOAuth ? 'Client Secret' : 'API Access Token',
				name1: isOAuth ? 'client_id' : 'api_key',
				name2: isOAuth ? 'client_secret' : 'password'
			};

			$row.find('.viw2s-new-credential-1').attr('placeholder', config.cred1).attr('name', 'viw2s_store_setting[0][' + config.name1 + ']');
			$row.find('.viw2s-new-credential-2').attr('placeholder', config.cred2).attr('name', 'viw2s_store_setting[0][' + config.name2 + ']');
		});

		$(document).on('change', '.viw2s-api-type-select', function () {
			var isOAuth = $(this).val() === 'oauth';
			var $row = $(this).closest('tr');
			var labels = {
				cred1: isOAuth ? 'Client ID' : 'API Key',
				cred2: isOAuth ? 'Client Secret' : 'API Access Token',
				name1: isOAuth ? 'client_id' : 'api_key',
				name2: isOAuth ? 'client_secret' : 'password'
			};

			var $credential1 = $row.find('td[data-label]').filter(function() {
				return $(this).attr('data-label') === 'Client ID' || $(this).attr('data-label') === 'API Key';
			}).first();
			if ($credential1.length) {
				$credential1.attr('data-label', labels.cred1).find('.viw2s-credential-label').text(labels.cred1);
				$credential1.find('input').attr('name', 'viw2s_store_setting[0][' + labels.name1 + ']');
			}

			var $credential2 = $row.find('td[data-label]').filter(function() {
				return $(this).attr('data-label') === 'Client Secret' || $(this).attr('data-label') === 'API Access Token';
			}).first();
			if ($credential2.length) {
				$credential2.attr('data-label', labels.cred2).find('.viw2s-credential-label').text(labels.cred2);
				$credential2.find('input').attr('name', 'viw2s_store_setting[0][' + labels.name2 + ']');
			}

			$row.find('input[name="viw2s_store_setting[0][existing_oauth]"], input[name="viw2s_store_setting[0][existing_legacy]"]').remove();
			var hiddenName = isOAuth ? 'existing_oauth' : 'existing_legacy';
			$row.find('td').first().append('<input type="hidden" name="viw2s_store_setting[0][' + hiddenName + ']" value="1">');
		});

	});

})(jQuery);
