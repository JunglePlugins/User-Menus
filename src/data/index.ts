/**
 * Data store for User Menus.
 */
import apiFetch from '@wordpress/api-fetch';
import { createReduxStore, register } from '@wordpress/data';
import type { MenuItemOptions, UserRole, UserCode } from '../../types/global';

export const STORE_NAME = 'user-menus';

interface StoreState {
	menuItems: Record<number, MenuItemOptions>;
	userRoles: UserRole[];
	userCodes: UserCode[];
	isLoading: boolean;
	error: string | null;
}

const DEFAULT_STATE: StoreState = {
	menuItems: {},
	userRoles: [],
	userCodes: [],
	isLoading: false,
	error: null,
};

// Action Types
type Action =
	| { type: 'SET_MENU_ITEM_OPTIONS'; itemId: number; options: MenuItemOptions }
	| { type: 'SET_USER_ROLES'; roles: UserRole[] }
	| { type: 'SET_USER_CODES'; codes: UserCode[] }
	| { type: 'SET_LOADING'; isLoading: boolean }
	| { type: 'SET_ERROR'; error: string | null };

const actions = {
	setMenuItemOptions(
		itemId: number,
		options: MenuItemOptions
	): Action {
		return {
			type: 'SET_MENU_ITEM_OPTIONS',
			itemId,
			options,
		};
	},

	setUserRoles( roles: UserRole[] ): Action {
		return {
			type: 'SET_USER_ROLES',
			roles,
		};
	},

	setUserCodes( codes: UserCode[] ): Action {
		return {
			type: 'SET_USER_CODES',
			codes,
		};
	},

	setLoading( isLoading: boolean ): Action {
		return {
			type: 'SET_LOADING',
			isLoading,
		};
	},

	setError( error: string | null ): Action {
		return {
			type: 'SET_ERROR',
			error,
		};
	},

	*fetchMenuItemOptions(
		itemId: number
	): Generator<Action | Promise<{ options: MenuItemOptions }>, void, { options: MenuItemOptions }> {
		yield actions.setLoading( true );
		try {
			const result: { options: MenuItemOptions } = yield apiFetch( {
				path: `/user-menus/v1/menu-items/${ itemId }`,
			} );
			yield actions.setMenuItemOptions( itemId, result.options );
		} catch ( error ) {
			yield actions.setError(
				error instanceof Error ? error.message : 'Unknown error'
			);
		}
		yield actions.setLoading( false );
	},

	*saveMenuItemOptions(
		itemId: number,
		options: MenuItemOptions
	): Generator<Action | Promise<{ options: MenuItemOptions }>, void, { options: MenuItemOptions }> {
		yield actions.setLoading( true );
		try {
			const result: { options: MenuItemOptions } = yield apiFetch( {
				path: `/user-menus/v1/menu-items/${ itemId }`,
				method: 'POST',
				data: options,
			} );
			yield actions.setMenuItemOptions( itemId, result.options );
		} catch ( error ) {
			yield actions.setError(
				error instanceof Error ? error.message : 'Unknown error'
			);
		}
		yield actions.setLoading( false );
	},

	*fetchUserRoles(): Generator<Action | Promise<UserRole[]>, void, UserRole[]> {
		try {
			const result: UserRole[] = yield apiFetch( {
				path: '/user-menus/v1/user-roles',
			} );
			yield actions.setUserRoles( result );
		} catch ( error ) {
			yield actions.setError(
				error instanceof Error ? error.message : 'Unknown error'
			);
		}
	},

	*fetchUserCodes(): Generator<Action | Promise<UserCode[]>, void, UserCode[]> {
		try {
			const result: UserCode[] = yield apiFetch( {
				path: '/user-menus/v1/user-codes',
			} );
			yield actions.setUserCodes( result );
		} catch ( error ) {
			yield actions.setError(
				error instanceof Error ? error.message : 'Unknown error'
			);
		}
	},
};

const reducer = (
	state: StoreState = DEFAULT_STATE,
	action: Action
): StoreState => {
	switch ( action.type ) {
		case 'SET_MENU_ITEM_OPTIONS':
			return {
				...state,
				menuItems: {
					...state.menuItems,
					[ action.itemId ]: action.options,
				},
			};

		case 'SET_USER_ROLES':
			return {
				...state,
				userRoles: action.roles,
			};

		case 'SET_USER_CODES':
			return {
				...state,
				userCodes: action.codes,
			};

		case 'SET_LOADING':
			return {
				...state,
				isLoading: action.isLoading,
			};

		case 'SET_ERROR':
			return {
				...state,
				error: action.error,
			};

		default:
			return state;
	}
};

const selectors = {
	getMenuItemOptions(
		state: StoreState,
		itemId: number
	): MenuItemOptions | null {
		return state.menuItems[ itemId ] || null;
	},

	getUserRoles( state: StoreState ): UserRole[] {
		return state.userRoles;
	},

	getUserCodes( state: StoreState ): UserCode[] {
		return state.userCodes;
	},

	isLoading( state: StoreState ): boolean {
		return state.isLoading;
	},

	getError( state: StoreState ): string | null {
		return state.error;
	},
};

const store = createReduxStore( STORE_NAME, {
	reducer,
	actions,
	selectors,
} );

register( store );

export { store };
