/**
 * Menu Item Fields Component.
 */
import { useState, useEffect } from '@wordpress/element';
import {
	SelectControl,
	RadioControl,
	CheckboxControl,
	TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type {
	MenuItemOptions,
	UserRole,
	UserCode,
	I18nStrings,
} from '../../types/global';

interface MenuItemFieldsProps {
	itemId: number;
	options: MenuItemOptions;
	itemObject: string;
	userRoles: Record<string, string> | UserRole[];
	userCodes: UserCode[];
	i18n: I18nStrings;
}

const MenuItemFields: React.FC<MenuItemFieldsProps> = ( {
	itemId,
	options,
	itemObject,
	userRoles,
	userCodes,
	i18n,
} ) => {
	const [ whichUsers, setWhichUsers ] = useState<
		'' | 'logged_in' | 'logged_out'
	>( options.which_users || '' );
	const [ canSee, setCanSee ] = useState<'yes' | 'no'>(
		options.can_see || 'yes'
	);
	const [ selectedRoles, setSelectedRoles ] = useState<string[]>(
		options.roles || []
	);
	const [ avatarSize, setAvatarSize ] = useState<number>(
		options.avatar_size || 24
	);
	const [ redirectType, setRedirectType ] = useState<
		'current' | 'home' | 'custom'
	>( options.redirect_type || 'current' );
	const [ redirectUrl, setRedirectUrl ] = useState<string>(
		options.redirect_url || ''
	);

	// Check if this is a special user link type
	const isUserLink = [ 'login', 'register', 'logout' ].includes( itemObject );
	const isLogoutLink = itemObject === 'logout';

	// Update hidden form fields when values change
	useEffect( () => {
		updateHiddenField( 'which-users', whichUsers );
	}, [ whichUsers ] );

	useEffect( () => {
		updateHiddenField( 'can-see', canSee );
	}, [ canSee ] );

	useEffect( () => {
		updateHiddenField( 'roles', selectedRoles.join( ',' ) );
	}, [ selectedRoles ] );

	useEffect( () => {
		updateHiddenField( 'avatar-size', String( avatarSize ) );
	}, [ avatarSize ] );

	useEffect( () => {
		updateHiddenField( 'redirect-type', redirectType );
	}, [ redirectType ] );

	useEffect( () => {
		updateHiddenField( 'redirect-url', redirectUrl );
	}, [ redirectUrl ] );

	const updateHiddenField = (
		fieldName: string,
		value: string
	): void => {
		const container = document.querySelector(
			`.user-menus-fields[data-item-id="${ itemId }"]`
		);
		if ( ! container ) return;

		const parent = container.closest( '.menu-item-settings' );
		if ( ! parent ) return;

		const field = parent.querySelector<HTMLInputElement>(
			`.um-${ fieldName }`
		);
		if ( field ) {
			field.value = value;
		}
	};

	const handleRoleChange = ( role: string, checked: boolean ): void => {
		if ( checked ) {
			setSelectedRoles( [ ...selectedRoles, role ] );
		} else {
			setSelectedRoles( selectedRoles.filter( ( r ) => r !== role ) );
		}
	};

	// Convert roles to array if object
	const rolesArray: UserRole[] = Array.isArray( userRoles )
		? userRoles
		: Object.entries( userRoles ).map( ( [ value, label ] ) => ( {
				value,
				label,
		  } ) );

	const whichUsersOptions = [
		{
			label: i18n.everyone || __( 'Everyone', 'user-menus' ),
			value: '',
		},
		{
			label:
				i18n.loggedOutUsers || __( 'Logged Out Users', 'user-menus' ),
			value: 'logged_out',
		},
		{
			label:
				i18n.loggedInUsers || __( 'Logged In Users', 'user-menus' ),
			value: 'logged_in',
		},
	];

	const redirectOptions = [
		{
			label: i18n.currentPage || __( 'Current Page', 'user-menus' ),
			value: 'current',
		},
		{
			label: i18n.homePage || __( 'Home Page', 'user-menus' ),
			value: 'home',
		},
		{
			label: i18n.customUrl || __( 'Custom URL', 'user-menus' ),
			value: 'custom',
		},
	];

	return (
		<div className="user-menus-fields-inner">
			{/* Avatar Size */}
			<div className="um-field um-field-avatar-size">
				<TextControl
					label={
						i18n.avatarSize || __( 'Avatar Size', 'user-menus' )
					}
					type="number"
					min={ 0 }
					step={ 1 }
					value={ String( avatarSize ) }
					onChange={ ( value ) =>
						setAvatarSize( parseInt( value, 10 ) || 24 )
					}
				/>
			</div>

			{/* Redirect Options - Only for user links */}
			{ isUserLink && (
				<>
					<div className="um-field um-field-redirect-type">
						<SelectControl
							label={
								i18n.redirectTo ||
								__(
									'Where should users be taken afterwards?',
									'user-menus'
								)
							}
							value={ redirectType }
							options={ redirectOptions }
							onChange={ ( value ) =>
								setRedirectType(
									value as 'current' | 'home' | 'custom'
								)
							}
						/>
					</div>

					{ redirectType === 'custom' && (
						<div className="um-field um-field-redirect-url">
							<TextControl
								label={
									i18n.enterCustomUrl ||
									__(
										'Enter a URL user should be redirected to',
										'user-menus'
									)
								}
								value={ redirectUrl }
								onChange={ setRedirectUrl }
								type="url"
							/>
						</div>
					) }

					{/* Fixed visibility for user links */}
					<div className="um-field um-field-which-users um-field-disabled">
						<SelectControl
							label={
								i18n.whoCanSee ||
								__( 'Who can see this link?', 'user-menus' )
							}
							value={ isLogoutLink ? 'logged_in' : 'logged_out' }
							options={ whichUsersOptions }
							disabled={ true }
						/>
						<p className="description">
							{ isLogoutLink
								? __(
										'Only logged in users can see the logout link.',
										'user-menus'
								  )
								: __(
										'Only logged out users can see login/register links.',
										'user-menus'
								  ) }
						</p>
					</div>
				</>
			) }

			{/* Visibility Options - For regular menu items */}
			{ ! isUserLink && (
				<>
					<div className="um-field um-field-which-users">
						<SelectControl
							label={
								i18n.whoCanSee ||
								__( 'Who can see this link?', 'user-menus' )
							}
							value={ whichUsers }
							options={ whichUsersOptions }
							onChange={ ( value ) =>
								setWhichUsers(
									value as '' | 'logged_in' | 'logged_out'
								)
							}
						/>
					</div>

					{ whichUsers === 'logged_in' && (
						<>
							<div className="um-field um-field-can-see">
								<RadioControl
									selected={ canSee }
									options={ [
										{
											label:
												i18n.chooseCanSee ||
												__(
													'Choose which roles can see this link',
													'user-menus'
												),
											value: 'yes',
										},
										{
											label:
												i18n.chooseCannotSee ||
												__(
													"Choose which roles won't see this link",
													'user-menus'
												),
											value: 'no',
										},
									] }
									onChange={ ( value ) =>
										setCanSee( value as 'yes' | 'no' )
									}
								/>
							</div>

							<div className="um-field um-field-roles">
								<div className="um-roles-list">
									{ rolesArray.map( ( role ) => (
										<CheckboxControl
											key={ role.value }
											label={ role.label }
											checked={ selectedRoles.includes(
												role.value
											) }
											onChange={ ( checked ) =>
												handleRoleChange(
													role.value,
													checked
												)
											}
										/>
									) ) }
								</div>
							</div>
						</>
					) }
				</>
			) }
		</div>
	);
};

export default MenuItemFields;
