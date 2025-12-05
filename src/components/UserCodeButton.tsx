/**
 * User Code Button Component.
 */
import { Button, Dropdown, MenuGroup, MenuItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import type { UserCode } from '../../types/global';

export interface UserCodeButtonProps {
	codes: UserCode[] | Record<string, string>;
	onInsert: ( code: string ) => void;
}

const UserCodeButton: React.FC<UserCodeButtonProps> = ( {
	codes,
	onInsert,
} ) => {
	// Convert codes to array format if it's an object
	const codesArray: UserCode[] = Array.isArray( codes )
		? codes
		: Object.entries( codes ).map( ( [ code, label ] ) => ( {
				code,
				label,
		  } ) );

	return (
		<Dropdown
			className="user-menus-user-code-dropdown"
			contentClassName="user-menus-user-code-content"
			popoverProps={ { placement: 'bottom-end' } }
			renderToggle={ ( { isOpen, onToggle } ) => (
				<Button
					onClick={ onToggle }
					aria-expanded={ isOpen }
					icon="admin-users"
					label={ __( 'Insert User Code', 'user-menus' ) }
					size="small"
				/>
			) }
			renderContent={ ( { onClose } ) => (
				<MenuGroup label={ __( 'User Codes', 'user-menus' ) }>
					{ codesArray.map( ( item ) => (
						<MenuItem
							key={ item.code }
							onClick={ () => {
								onInsert( `{${ item.code }}` );
								onClose();
							} }
						>
							{ item.label }
						</MenuItem>
					) ) }
				</MenuGroup>
			) }
		/>
	);
};

export default UserCodeButton;
