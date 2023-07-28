<?php
defined( 'ABSPATH' ) || exit;
use FileBird\Controller\UserSettings;
use FileBird\Classes\Helpers;
$userSettings = UserSettings::getInstance();

$theme             = $userSettings->settings['theme'];
$folderCounterType = $userSettings->settings['folder_counter_type'];
$showBreadCrumb    = $userSettings->settings['showBreadCrumb'];
$isActivated       = Helpers::isActivated();
?>
<table class="form-table">
    <tr>
        <th scope="row">
            <label for="njt_fbv_folder_per_user"><?php esc_html_e( 'Each user has his own folders?', 'filebird' ); ?>
            </label>
        </th>
        <td>
            <label class="njt-switch">
                <input type="checkbox" name="njt_fbv_folder_per_user" class="njt-submittable"
                    id="njt_fbv_folder_per_user" value="1"
                    <?php checked( get_option( 'njt_fbv_folder_per_user' ), '1' ); ?> />
                <span class="slider round">
                    <span class="njt-switch-cursor"></span>
                </span>
            </label>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="njt_fbv_show_breadcrumb"><?php esc_html_e( 'Show Breadcrumb', 'filebird' ); ?></label>
        </th>
        <td>
            <label class="njt-switch">
                <input type="checkbox" name="showBreadCrumb" class="njt-submittable" id="njt_fbv_show_breadcrumb"
                    value="1" <?php checked( $showBreadCrumb, '1' ); ?> />
                <span class="slider round">
                    <span class="njt-switch-cursor"></span>
                </span>
            </label>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label><?php esc_html_e( 'Folder Counter', 'filebird' ); ?></label>
        </th>
        <td>
            <select name="folderCounterType">
                <option value="counter_file_in_folder"
                    <?php selected( $folderCounterType, 'counter_file_in_folder' ); ?>>
                    <?php esc_html_e( 'Count files in each folder', 'filebird' ); ?>
                </option>
                <option value="counter_file_in_folder_and_sub"
                    <?php selected( $folderCounterType, 'counter_file_in_folder_and_sub' ); ?>>
                    <?php esc_html_e( 'Count files in both parent folder and subfolders', 'filebird' ); ?>
                </option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label><?php esc_html_e( 'FileBird Theme', 'filebird' ); ?></label>
        </th>
        <td>
            <div class="fbv-theme">
                <div class="fbv-radio-select-img">
                    <input type="radio" id="default" name="theme" value="default"
                        <?php checked( $theme['themeName'], '', true ); ?>>
                    <label for="default">
                        <div class="fbv-radio-img-wrap">
                            <img src="<?php echo NJFB_PLUGIN_URL . 'assets/img/default.svg'; ?>">
                        </div>
                        <span><?php esc_html_e( 'FileBird Default', 'filebird' ); ?></span>
                    </label>
                </div>
                <div class="fbv-radio-select-img <?php echo esc_attr( ! $isActivated ? 'fbv-pro-feature' : '' ); ?>">
                    <input type="radio" id="windows" name="theme" value="windows"
                        <?php checked( $theme['themeName'], 'windows', true ); ?>>
                    <label for="windows">
                        <div class="fbv-radio-img-wrap">
                            <img src="<?php echo NJFB_PLUGIN_URL . 'assets/img/windows.svg'; ?>">
                        </div>
                        <span><?php esc_html_e( 'Windows 11', 'filebird' ); ?></span>
                    </label>
                </div>
                <div class="fbv-radio-select-img <?php echo esc_attr( ! $isActivated ? 'fbv-pro-feature' : '' ); ?>">
                    <input type="radio" id="dropbox" name="theme" value="dropbox"
                        <?php checked( $theme['themeName'], 'dropbox', true ); ?>>
                    <label for="dropbox">
                        <div class="fbv-radio-img-wrap">
                            <img src="<?php echo NJFB_PLUGIN_URL . 'assets/img/dropbox.svg'; ?>">
                        </div>
                        <span><?php esc_html_e( 'Dropbox', 'filebird' ); ?></span>
                    </label>
                </div>
            </div>
        </td>
    </tr>
</table>

<button type="button" id="fbv-save-settings-submit" class="button button-primary njt-button-loading">
    <?php esc_html_e( 'Save Changes', 'filebird' ); ?>
</button>