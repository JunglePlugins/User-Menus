/**
 * Global type declarations for User Menus.
 */

/** User role definition */
export interface UserRole {
	value: string;
	label: string;
}

/** User code definition */
export interface UserCode {
	code: string;
	label: string;
}

/** Menu item options */
export interface MenuItemOptions {
	which_users: '' | 'logged_in' | 'logged_out';
	can_see: 'yes' | 'no';
	roles: string[];
	avatar_size: number;
	redirect_type: 'current' | 'home' | 'custom';
	redirect_url: string;
}

/** i18n strings */
export interface I18nStrings {
	whoCanSee?: string;
	everyone?: string;
	loggedInUsers?: string;
	loggedOutUsers?: string;
	chooseCanSee?: string;
	chooseCannotSee?: string;
	avatarSize?: string;
	redirectTo?: string;
	currentPage?: string;
	homePage?: string;
	customUrl?: string;
	enterCustomUrl?: string;
	insertUserCode?: string;
	userLink?: string;
}

/** Menu editor configuration from PHP */
export interface MenuEditorConfig {
	adminUrl: string;
	pluginUrl: string;
	userRoles: Record<string, string> | UserRole[];
	userCodes: UserCode[];
	defaults: MenuItemOptions;
	i18n: I18nStrings;
}

/** Data passed for each menu item */
export interface MenuItemData {
	options: MenuItemOptions;
	itemObject: string;
}

/** Visibility change event data */
export interface VisibilityChangeData {
	which_users: '' | 'logged_in' | 'logged_out';
	can_see: 'yes' | 'no';
	roles: string[];
}

declare global {
	interface Window {
		userMenusMenuEditor?: MenuEditorConfig;
		userMenusData?: {
			restBase: string;
			nonce: string;
		};
	}
}

export {};
