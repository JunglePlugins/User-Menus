/**
 * Menu Editor Entry Point.
 */
import { createRoot } from '@wordpress/element';
import MenuItemFields from './MenuItemFields';
import './style.scss';
import type { MenuEditorConfig, MenuItemData, UserCode } from '../../types/global';

declare const window: Window & {
	userMenusMenuEditor?: MenuEditorConfig;
};

/**
 * Initialize the menu editor enhancements.
 */
const init = (): void => {
	// Get configuration from localized data
	const config: MenuEditorConfig = window.userMenusMenuEditor || {
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

	// Initialize each menu item field container
	const containers = document.querySelectorAll<HTMLElement>(
		'.user-menus-fields'
	);

	containers.forEach( ( container ) => {
		const itemId = container.dataset.itemId;
		const dataScript = container.parentNode?.querySelector(
			'.user-menus-item-data'
		);

		if ( ! dataScript || ! itemId ) {
			return;
		}

		let data: MenuItemData;
		try {
			data = JSON.parse( dataScript.textContent || '' );
		} catch ( e ) {
			console.error( 'Failed to parse menu item data:', e );
			return;
		}

		const root = createRoot( container );
		root.render(
			<MenuItemFields
				itemId={ parseInt( itemId, 10 ) }
				options={ data.options }
				itemObject={ data.itemObject }
				userRoles={ config.userRoles || {} }
				userCodes={ config.userCodes || [] }
				i18n={ config.i18n || {} }
			/>
		);
	} );

	// Initialize user code buttons for title inputs
	initUserCodeButtons( config.userCodes || [] );
};

/**
 * Initialize user code buttons next to title inputs.
 */
const initUserCodeButtons = ( codes: UserCode[] ): void => {
	const config = window.userMenusMenuEditor;
	const menuItems = document.querySelectorAll<HTMLElement>(
		'.menu-item-settings'
	);

	menuItems.forEach( ( menuItem ) => {
		const titleInput =
			menuItem.querySelector<HTMLInputElement>( '.edit-menu-item-title' );
		if ( ! titleInput ) {
			return;
		}

		// Check if button already exists
		if ( menuItem.querySelector( '.user-menus-code-btn' ) ) {
			return;
		}

		// Create button container
		const btnContainer = document.createElement( 'span' );
		btnContainer.className = 'user-menus-code-btn';

		// Create button
		const btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = 'button button-secondary';
		btn.innerHTML = '<span class="dashicons dashicons-admin-users"></span>';
		btn.title = config?.i18n?.insertUserCode || 'Insert User Code';

		// Create dropdown
		const dropdown = document.createElement( 'ul' );
		dropdown.className = 'user-menus-code-dropdown';
		dropdown.style.display = 'none';

		codes.forEach( ( item ) => {
			const li = document.createElement( 'li' );
			const a = document.createElement( 'a' );
			a.href = '#';
			a.textContent = item.label;
			a.dataset.code = item.code;
			a.addEventListener( 'click', ( e: MouseEvent ) => {
				e.preventDefault();
				insertAtCursor( titleInput, `{${ item.code }}` );
				dropdown.style.display = 'none';
			} );
			li.appendChild( a );
			dropdown.appendChild( li );
		} );

		btn.addEventListener( 'click', ( e: MouseEvent ) => {
			e.preventDefault();
			dropdown.style.display =
				dropdown.style.display === 'none' ? 'block' : 'none';
		} );

		// Close dropdown when clicking outside
		document.addEventListener( 'click', ( e: MouseEvent ) => {
			if ( ! btnContainer.contains( e.target as Node ) ) {
				dropdown.style.display = 'none';
			}
		} );

		btnContainer.appendChild( btn );
		btnContainer.appendChild( dropdown );

		// Insert after the title input's label
		const titleLabel = titleInput.closest( '.field-title' );
		if ( titleLabel ) {
			const label = titleLabel.querySelector( 'label' );
			if ( label ) {
				label.style.display = 'flex';
				label.style.alignItems = 'center';
				label.style.gap = '5px';
				label.appendChild( btnContainer );
			}
		}
	} );
};

/**
 * Insert text at cursor position in input.
 */
const insertAtCursor = (
	input: HTMLInputElement,
	text: string
): void => {
	const start = input.selectionStart || 0;
	const end = input.selectionEnd || 0;
	const value = input.value;

	input.value = value.substring( 0, start ) + text + value.substring( end );
	input.selectionStart = input.selectionEnd = start + text.length;
	input.focus();

	// Trigger change event
	const event = new Event( 'input', { bubbles: true } );
	input.dispatchEvent( event );
};

// Export for use in other scripts
export { init };

// Auto-initialize when DOM is ready
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}
