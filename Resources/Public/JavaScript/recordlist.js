(function() {
	var T3 = window.T3 || {};
	window.T3 = T3;
	T3.Admin = T3.Admin || {};
	T3.Admin.RecordList = T3.Admin.RecordList || {};
	var $ = window.jQuery;

	T3.Admin.RecordList.init = function($recordList) {
		var resultListing = ResultListing.create({
			$container: $recordList
		});
		resultListing.bootstrap();
	};

	var ResultListing = Ember.Object.extend({
		$container: null,
		_$actionBar: null,

		currentSelection: Ember.Set.create(),

		numberOfSelectedElementsBinding: 'currentSelection.length',

		actionBarVisible: function() {
			return this.get('numberOfSelectedElements') > 0;
		}.property('numberOfSelectedElements').cacheable(),

		multipleSelectionActive: function() {
			return this.get('numberOfSelectedElements') > 1;
		}.property('numberOfSelectedElements').cacheable(),

		toggleActionBar: function() {
			if (this.get('actionBarVisible')) {
				this._$actionBar.removeClass('hiddenActionBar');
				this.$container.find('[data-area=main]').addClass('span8').removeClass('span10');
			} else {
				this._$actionBar.addClass('hiddenActionBar');
				var that = this;
				window.setTimeout(function() {
					that.$container.find('[data-area=main]').removeClass('span8').addClass('span10');
				}, 230);

			}
		}.observes('actionBarVisible'),

		/**
		 * INITIALIZATION
		 */
		init: function() {
			this._$actionBar = this.$container.find('[data-area=actionBar]');
		},

		bootstrap: function() {
			var that = this;

			this.$container.on('click', '[data-area=records] *', function() {
				var $element = $(this);
				$element = $element.closest('[data-identifier]');
				if ($element.hasClass('typo3-admin-active')) {
					$element.removeClass('typo3-admin-active');
					that.currentSelection.remove($element.attr('data-identifier'));
				} else {
					$element.addClass('typo3-admin-active');
					that.currentSelection.add($element.attr('data-identifier'));
				}
			});

			this._initializeActionBar();
		},

		pageLoaded: function($containerOnWebsite, $containerFromServer) {
			$containerOnWebsite.find('[data-area=searchParameters]').html($containerFromServer.find('[data-area=searchParameters]').html());
			$containerOnWebsite.find('[data-area=results]').html($containerFromServer.find('[data-area=results]').html());

			var nodesToSelectAgain = [];
			this.currentSelection.forEach(function(identifier) {
				var $checkbox = $containerOnWebsite.find('[data-area=results] input[name="selection[]"][value="' + identifier + '"]');
				if ($checkbox.length > 0) {
					nodesToSelectAgain.push($checkbox);
				}
			});
			this.currentSelection.clear();
			nodesToSelectAgain.forEach(function($node) {
				$node.attr('checked', 'checked');
				$node.trigger('change');
			})
		},

		_initializeActionBar: function() {
			var that = this;
			var actionBarView = Ember.View.create({
				template: Ember.Handlebars.compile(this._$actionBar.html()),
				templateContext: this,
				// we also need to set this private member, to supposedly work around an ember bug.
				_templateContext: this,
				didInsertElement: function() {
					this.$().find('a').click(function(e) {
						that.showAdminController($(this).attr('href'), $(this).text());
						e.preventDefault();
					});
				}
			});
			actionBarView.replaceIn(this._$actionBar);
		},

		showAdminController: function(uri, title) {
			// Open an admin controller based on URI and currentSelection

			this.get('currentSelection').forEach(function(identifier) {
				uri += encodeURI('&moduleArguments[--adminRuntime][object][]=') + encodeURIComponent(identifier);
			});
			uri += encodeURI('&moduleArguments[hideModuleDecoration]=1');

			var $dialog = $('<div></div>')
				.html('<iframe style="border: 0px; " src="' + uri + '" width="100%" height="100%"></iframe>')
				.dialog({
					autoOpen: true,
					modal: true,
					height: 525,
					width: 800,
					title: title,
					zIndex: 10050
				});
		}
	});

})();