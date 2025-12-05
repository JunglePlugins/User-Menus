/**
 * Role Selector Component.
 */
import { CheckboxControl } from '@wordpress/components';
import type { UserRole } from '../../types/global';

export interface RoleSelectorProps {
	selectedRoles: string[];
	userRoles: Record<string, string> | UserRole[];
	onChange: ( roles: string[] ) => void;
}

const RoleSelector: React.FC<RoleSelectorProps> = ( {
	selectedRoles,
	userRoles,
	onChange,
} ) => {
	const handleRoleChange = ( role: string, isChecked: boolean ): void => {
		let newRoles: string[];
		if ( isChecked ) {
			newRoles = [ ...selectedRoles, role ];
		} else {
			newRoles = selectedRoles.filter( ( r ) => r !== role );
		}
		onChange( newRoles );
	};

	// Convert userRoles to array format if it's an object
	const rolesArray: UserRole[] = Array.isArray( userRoles )
		? userRoles
		: Object.entries( userRoles ).map( ( [ value, label ] ) => ( {
				value,
				label,
		  } ) );

	return (
		<div className="user-menus-role-selector">
			<div className="user-menus-role-list">
				{ rolesArray.map( ( role ) => {
					const roleValue = role.value;
					const roleLabel = role.label;

					return (
						<CheckboxControl
							key={ roleValue }
							label={ roleLabel }
							checked={ selectedRoles.includes( roleValue ) }
							onChange={ ( checked ) =>
								handleRoleChange( roleValue, checked )
							}
						/>
					);
				} ) }
			</div>
		</div>
	);
};

export default RoleSelector;
