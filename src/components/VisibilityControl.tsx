/**
 * Visibility Control Component.
 */
import { SelectControl, RadioControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import RoleSelector from './RoleSelector';
import type { UserRole, VisibilityChangeData } from '../../types/global';

export interface VisibilityControlProps {
	whichUsers: '' | 'logged_in' | 'logged_out';
	canSee: 'yes' | 'no';
	roles: string[];
	userRoles: Record<string, string> | UserRole[];
	onChange: ( data: VisibilityChangeData ) => void;
	disabled?: boolean;
}

const VisibilityControl: React.FC<VisibilityControlProps> = ( {
	whichUsers,
	canSee,
	roles,
	userRoles,
	onChange,
	disabled = false,
} ) => {
	const [ localWhichUsers, setLocalWhichUsers ] = useState<
		'' | 'logged_in' | 'logged_out'
	>( whichUsers || '' );
	const [ localCanSee, setLocalCanSee ] = useState<'yes' | 'no'>(
		canSee || 'yes'
	);
	const [ localRoles, setLocalRoles ] = useState<string[]>( roles || [] );

	useEffect( () => {
		setLocalWhichUsers( whichUsers || '' );
		setLocalCanSee( canSee || 'yes' );
		setLocalRoles( roles || [] );
	}, [ whichUsers, canSee, roles ] );

	const handleWhichUsersChange = (
		value: '' | 'logged_in' | 'logged_out'
	): void => {
		setLocalWhichUsers( value );
		if ( value !== 'logged_in' ) {
			setLocalRoles( [] );
		}
		onChange( {
			which_users: value,
			can_see: localCanSee,
			roles: value !== 'logged_in' ? [] : localRoles,
		} );
	};

	const handleCanSeeChange = ( value: 'yes' | 'no' ): void => {
		setLocalCanSee( value );
		onChange( {
			which_users: localWhichUsers,
			can_see: value,
			roles: localRoles,
		} );
	};

	const handleRolesChange = ( newRoles: string[] ): void => {
		setLocalRoles( newRoles );
		onChange( {
			which_users: localWhichUsers,
			can_see: localCanSee,
			roles: newRoles,
		} );
	};

	const whichUsersOptions = [
		{ label: __( 'Everyone', 'user-menus' ), value: '' },
		{
			label: __( 'Logged Out Users', 'user-menus' ),
			value: 'logged_out',
		},
		{ label: __( 'Logged In Users', 'user-menus' ), value: 'logged_in' },
	];

	return (
		<div className="user-menus-visibility-control">
			<SelectControl
				label={ __( 'Who can see this link?', 'user-menus' ) }
				value={ localWhichUsers }
				options={ whichUsersOptions }
				onChange={ ( value ) =>
					handleWhichUsersChange(
						value as '' | 'logged_in' | 'logged_out'
					)
				}
				disabled={ disabled }
			/>

			{ localWhichUsers === 'logged_in' && (
				<>
					<RadioControl
						selected={ localCanSee }
						options={ [
							{
								label: __(
									'Choose which roles can see this link',
									'user-menus'
								),
								value: 'yes',
							},
							{
								label: __(
									"Choose which roles won't see this link",
									'user-menus'
								),
								value: 'no',
							},
						] }
						onChange={ ( value ) =>
							handleCanSeeChange( value as 'yes' | 'no' )
						}
					/>

					<RoleSelector
						selectedRoles={ localRoles }
						userRoles={ userRoles }
						onChange={ handleRolesChange }
					/>
				</>
			) }
		</div>
	);
};

export default VisibilityControl;
