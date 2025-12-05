/**
 * User Menus - Menu Editor
 * Pre-compiled bundle using WordPress global dependencies
 */
(function() {
	'use strict';

	// WordPress dependencies from globals
	const { createElement, useState, useEffect, createRoot, Fragment } = wp.element;
	const { SelectControl, RadioControl, CheckboxControl, TextControl, Button } = wp.components;
	const { __ } = wp.i18n;

	// Get configuration from localized data
	const getConfig = () => window.userMenusMenuEditor || {
		adminUrl: '',
		pluginUrl: '',
		userRoles: {},
		userCodes: [],
		defaults: {
			which_users: '',
			can_see: 'yes',
			roles: [],
			avatar_size: 24,
			redirect_type: 'current',
			redirect_url: '',
		},
		i18n: {},
	};

	/**
	 * Update hidden form field value
	 */
	const updateHiddenField = (itemId, fieldName, value) => {
		const container = document.querySelector(
			`.user-menus-fields[data-item-id="${itemId}"]`
		);
		if (!container) return;

		const parent = container.closest('.menu-item-settings');
		if (!parent) return;

		const field = parent.querySelector(`.um-${fieldName}`);
		if (field) {
			field.value = value;
		}
	};

	/**
	 * Menu Item Fields Component
	 */
	const MenuItemFields = ({ itemId, options, itemObject, userRoles, userCodes, i18n }) => {
		const [whichUsers, setWhichUsers] = useState(options.which_users || '');
		const [canSee, setCanSee] = useState(options.can_see || 'yes');
		const [selectedRoles, setSelectedRoles] = useState(options.roles || []);
		const [avatarSize, setAvatarSize] = useState(options.avatar_size || 24);
		const [redirectType, setRedirectType] = useState(options.redirect_type || 'current');
		const [redirectUrl, setRedirectUrl] = useState(options.redirect_url || '');

		// Check if this is a special user link type
		const isUserLink = ['login', 'register', 'logout'].includes(itemObject);
		const isLogoutLink = itemObject === 'logout';

		// Update hidden form fields when values change
		useEffect(() => {
			updateHiddenField(itemId, 'which-users', whichUsers);
		}, [whichUsers, itemId]);

		useEffect(() => {
			updateHiddenField(itemId, 'can-see', canSee);
		}, [canSee, itemId]);

		useEffect(() => {
			updateHiddenField(itemId, 'roles', selectedRoles.join(','));
		}, [selectedRoles, itemId]);

		useEffect(() => {
			updateHiddenField(itemId, 'avatar-size', String(avatarSize));
		}, [avatarSize, itemId]);

		useEffect(() => {
			updateHiddenField(itemId, 'redirect-type', redirectType);
		}, [redirectType, itemId]);

		useEffect(() => {
			updateHiddenField(itemId, 'redirect-url', redirectUrl);
		}, [redirectUrl, itemId]);

		const handleRoleChange = (role, checked) => {
			if (checked) {
				setSelectedRoles([...selectedRoles, role]);
			} else {
				setSelectedRoles(selectedRoles.filter((r) => r !== role));
			}
		};

		// Convert roles to array if object
		const rolesArray = Array.isArray(userRoles)
			? userRoles
			: Object.entries(userRoles).map(([value, label]) => ({
				value,
				label,
			}));

		const whichUsersOptions = [
			{
				label: i18n.everyone || __('Everyone', 'user-menus'),
				value: '',
			},
			{
				label: i18n.loggedOutUsers || __('Logged Out Users', 'user-menus'),
				value: 'logged_out',
			},
			{
				label: i18n.loggedInUsers || __('Logged In Users', 'user-menus'),
				value: 'logged_in',
			},
		];

		const redirectOptions = [
			{
				label: i18n.currentPage || __('Current Page', 'user-menus'),
				value: 'current',
			},
			{
				label: i18n.homePage || __('Home Page', 'user-menus'),
				value: 'home',
			},
			{
				label: i18n.customUrl || __('Custom URL', 'user-menus'),
				value: 'custom',
			},
		];

		return createElement('div', { className: 'user-menus-fields-inner' },
			// Avatar Size
			createElement('div', { className: 'um-field um-field-avatar-size' },
				createElement(TextControl, {
					label: i18n.avatarSize || __('Avatar Size', 'user-menus'),
					type: 'number',
					min: 0,
					step: 1,
					value: String(avatarSize),
					onChange: (value) => setAvatarSize(parseInt(value, 10) || 24),
				})
			),

			// Redirect Options - Only for user links
			isUserLink && createElement(Fragment, null,
				createElement('div', { className: 'um-field um-field-redirect-type' },
					createElement(SelectControl, {
						label: i18n.redirectTo || __('Where should users be taken afterwards?', 'user-menus'),
						value: redirectType,
						options: redirectOptions,
						onChange: (value) => setRedirectType(value),
					})
				),

				redirectType === 'custom' && createElement('div', { className: 'um-field um-field-redirect-url' },
					createElement(TextControl, {
						label: i18n.enterCustomUrl || __('Enter a URL user should be redirected to', 'user-menus'),
						value: redirectUrl,
						onChange: setRedirectUrl,
						type: 'url',
					})
				),

				// Fixed visibility for user links
				createElement('div', { className: 'um-field um-field-which-users um-field-disabled' },
					createElement(SelectControl, {
						label: i18n.whoCanSee || __('Who can see this link?', 'user-menus'),
						value: isLogoutLink ? 'logged_in' : 'logged_out',
						options: whichUsersOptions,
						disabled: true,
					}),
					createElement('p', { className: 'description' },
						isLogoutLink
							? __('Only logged in users can see the logout link.', 'user-menus')
							: __('Only logged out users can see login/register links.', 'user-menus')
					)
				)
			),

			// Visibility Options - For regular menu items
			!isUserLink && createElement(Fragment, null,
				createElement('div', { className: 'um-field um-field-which-users' },
					createElement(SelectControl, {
						label: i18n.whoCanSee || __('Who can see this link?', 'user-menus'),
						value: whichUsers,
						options: whichUsersOptions,
						onChange: (value) => setWhichUsers(value),
					})
				),

				whichUsers === 'logged_in' && createElement(Fragment, null,
					createElement('div', { className: 'um-field um-field-can-see' },
						createElement(RadioControl, {
							selected: canSee,
							options: [
								{
									label: i18n.chooseCanSee || __('Choose which roles can see this link', 'user-menus'),
									value: 'yes',
								},
								{
									label: i18n.chooseCannotSee || __("Choose which roles won't see this link", 'user-menus'),
									value: 'no',
								},
							],
							onChange: (value) => setCanSee(value),
						})
					),

					createElement('div', { className: 'um-field um-field-roles' },
						createElement('div', { className: 'um-roles-list' },
							rolesArray.map((role) =>
								createElement(CheckboxControl, {
									key: role.value,
									label: role.label,
									checked: selectedRoles.includes(role.value),
									onChange: (checked) => handleRoleChange(role.value, checked),
								})
							)
						)
					)
				)
			)
		);
	};

	/**
	 * Insert text at cursor position in input
	 */
	const insertAtCursor = (input, text) => {
		const start = input.selectionStart || 0;
		const end = input.selectionEnd || 0;
		const value = input.value;

		input.value = value.substring(0, start) + text + value.substring(end);
		input.selectionStart = input.selectionEnd = start + text.length;
		input.focus();

		// Trigger change event
		const event = new Event('input', { bubbles: true });
		input.dispatchEvent(event);
	};

	/**
	 * Initialize user code buttons next to title inputs
	 */
	const initUserCodeButtons = (codes) => {
		const config = getConfig();
		const menuItems = document.querySelectorAll('.menu-item-settings');

		menuItems.forEach((menuItem) => {
			const titleInput = menuItem.querySelector('.edit-menu-item-title');
			if (!titleInput) {
				return;
			}

			// Check if button already exists
			if (menuItem.querySelector('.user-menus-code-btn')) {
				return;
			}

			// Create button container
			const btnContainer = document.createElement('span');
			btnContainer.className = 'user-menus-code-btn';

			// Create button
			const btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'button button-secondary';
			btn.innerHTML = '<span class="dashicons dashicons-admin-users"></span>';
			btn.title = config.i18n?.insertUserCode || 'Insert User Code';

			// Create dropdown
			const dropdown = document.createElement('ul');
			dropdown.className = 'user-menus-code-dropdown';
			dropdown.style.display = 'none';

			codes.forEach((item) => {
				const li = document.createElement('li');
				const a = document.createElement('a');
				a.href = '#';
				a.textContent = item.label;
				a.dataset.code = item.code;
				a.addEventListener('click', (e) => {
					e.preventDefault();
					insertAtCursor(titleInput, `{${item.code}}`);
					dropdown.style.display = 'none';
				});
				li.appendChild(a);
				dropdown.appendChild(li);
			});

			btn.addEventListener('click', (e) => {
				e.preventDefault();
				dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
			});

			// Close dropdown when clicking outside
			document.addEventListener('click', (e) => {
				if (!btnContainer.contains(e.target)) {
					dropdown.style.display = 'none';
				}
			});

			btnContainer.appendChild(btn);
			btnContainer.appendChild(dropdown);

			// Insert after the title input's label
			const titleLabel = titleInput.closest('.field-title');
			if (titleLabel) {
				const label = titleLabel.querySelector('label');
				if (label) {
					label.style.display = 'flex';
					label.style.alignItems = 'center';
					label.style.gap = '5px';
					label.appendChild(btnContainer);
				}
			}
		});
	};

	/**
	 * Initialize the menu editor enhancements
	 */
	const init = () => {
		const config = getConfig();

		// Initialize each menu item field container
		const containers = document.querySelectorAll('.user-menus-fields');

		containers.forEach((container) => {
			const itemId = container.dataset.itemId;
			const dataScript = container.parentNode?.querySelector('.user-menus-item-data');

			if (!dataScript || !itemId) {
				return;
			}

			let data;
			try {
				data = JSON.parse(dataScript.textContent || '');
			} catch (e) {
				console.error('User Menus: Failed to parse menu item data:', e);
				return;
			}

			const root = createRoot(container);
			root.render(
				createElement(MenuItemFields, {
					itemId: parseInt(itemId, 10),
					options: data.options,
					itemObject: data.itemObject,
					userRoles: config.userRoles || {},
					userCodes: config.userCodes || [],
					i18n: config.i18n || {},
				})
			);
		});

		// Initialize user code buttons for title inputs
		initUserCodeButtons(config.userCodes || []);
	};

	// Export for use in other scripts
	window.userMenus = window.userMenus || {};
	window.userMenus.menuEditor = { init };

	// Auto-initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
