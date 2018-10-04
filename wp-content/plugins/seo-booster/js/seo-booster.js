/* globals HS: true, sbbeacondata:true */
jQuery(document).ready(function($) {
/*
	Helpscout beacon embed
	*/


	var Beacon_Setup = {
		init : function() {
			this.register();
			this.config();
			// this.processTriggers();
		},
		register : function() {
			// Register beacon, ported from official Beacon embed code
			! function(e, o, n) {
				window.HSCW = o, window.HS = n, n.beacon = n.beacon || {};
				var t = n.beacon;

				t.userConfig = {}, t.readyQueue = [], t.config = function(e) {
					this.userConfig = e;
				}, t.ready = function(e) {
					this.readyQueue.push(e);
				}, o.config = {
					docs: {
						enabled: true,
						baseUrl: 'https://cleverplugins.helpscoutdocs.com/'
					},
					contact: {
						enabled: sbbeacondata.enablecontact,
						formId: "180a2252-39bf-11e8-8d65-0ee9bb0328ce"
					}
				};
				var r = e.getElementsByTagName('script')[0], c = e.createElement('script');
				c.type = 'text/javascript', c.async = !0, c.src = 'https://djtflbt20bdde.cloudfront.net/', r.parentNode.insertBefore(c, r);
			}(document, window.HSCW || {}, window.HS || {});
		},
		config : function() {
			HS.beacon.config({
				modal: false,
				color: '#36ace0',
				autoInit: true,
				attachment: false,
				instructions: sbbeacondata.instructions,
				collection: '59208aa10428634b4a334eaf',
				topArticles: true,
				showContactFields: true,
				showName:true,
				// poweredBy: 'helpie',
				// icon: beacon_vars.icon,
				// position: beacon_vars.position,
				// zIndex: parseInt(beacon_vars.zindex),
				// showSubject: beacon_vars.show_subject,
				// translation: {
				//     searchLabel: beacon_vars.search_label,
				//     searchErrorLabel: beacon_vars.search_error_label,
				//     noResultsLabel: beacon_vars.no_results_label,
				//     contactLabel: beacon_vars.contact_label,
				//     attachFileLabel: beacon_vars.attach_file_label,
				//     attachFileError: beacon_vars.attach_file_error,
				//     fileExtensionError: beacon_vars.file_extension_error,
				//     nameLabel: beacon_vars.name_label,
				//     nameError: beacon_vars.name_error,
				//     emailLabel: beacon_vars.email_label,
				//     emailError: beacon_vars.email_error,
				//     topicLabel: beacon_vars.topic_label,
				//     topicError: beacon_vars.topic_error,
				//     subjectLabel: beacon_vars.subject_label,
				//     subjectError: beacon_vars.subject_error,
				//     messageLabel: beacon_vars.message_label,
				//     messageError: beacon_vars.message_error,
				//     sendLabel: beacon_vars.send_label,
				//     contactSuccessLabel: beacon_vars.success_label,
				//     contactSuccessDescription: beacon_vars.success_desc
				// }
			});
		},
				/*
				processTriggers : function () {
						// ID-based triggers
						$('#beacon-open').click(function(e) {
								e.preventDefault();
								HS.beacon.open();
						});
						$('#beacon-close').click(function(e) {
								e.preventDefault();
								HS.beacon.close();
						});
						$('#beacon-toggle').click(function(e) {
								e.preventDefault();
								HS.beacon.toggle();
						});

						// Class-based triggers
						$('.beacon-open').click(function(e) {
								e.preventDefault();
								HS.beacon.open();
						});
						$('.beacon-close').click(function(e) {
								e.preventDefault();
								HS.beacon.close();
						});
						$('.beacon-toggle').click(function(e) {
								e.preventDefault();
								HS.beacon.toggle();
						});
						$('.beacon-article-link').click(function(e) {
								e.preventDefault();
						});

						// Core modal trigger
						if ( beacon_vars.modal === 'true' ) {
								$('.show-beacon.menu-item a').click(function(e) {
										e.preventDefault();

										HS.beacon.open();
								});

								$('.show-beacon').click(function(e) {
										e.preventDefault();

										HS.beacon.open();
								});
						}
				}
				*/
			};
			Beacon_Setup.init();


			HS.beacon.ready(function () {
				// http://developer.helpscout.net/beacons/javascript-api/#identify
				HS.beacon.identify({
					name: sbbeacondata.user_name,
					email: sbbeacondata.email
				});
				// http://developer.helpscout.net/beacons/javascript-api/#prefill
				//     HS.beacon.prefill({});
			});
/*
	jQuery('.pagespeed .suggestions .suggtitle').on('hover', function() {
		jQuery(this).parent().find('.sugglist').slideDown();
	});
*/


	// Changes the css and visual appearance of some settings in SEO Booster 2 if the "Use Dynamic Tagging" feature is turned on.
	jQuery('#seobooster_dynamic_tagging').on('click', function() {

		if ($(this).prop('checked')) {

			$('.taggingrelated').each( function( i, elem ) {
				$(elem).removeClass('muted');
			});

		}
		else {
			$('.taggingrelated').each( function( i, elem ) {
				$(elem).addClass('muted');
			});
		}
	});

	// Hide internal buttons related to internal linking
	jQuery('#seobooster_internal_linking').on('click', function() {

		if ($(this).prop('checked')) {

			$('.linkingrelated').each( function( i, elem ) {
				$(elem).removeClass('muted');
			});

		}
		else {
			$('.linkingrelated').each( function( i, elem ) {
				$(elem).addClass('muted');
			});
		}
	});




	// todo - check if any images needs to be lazy loaded before running the script
	// todo - any lazy load library included with WP?
	jQuery("img.lazy").lazyload();

});