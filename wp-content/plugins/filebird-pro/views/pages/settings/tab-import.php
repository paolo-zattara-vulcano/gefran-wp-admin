<?php if ( $data_import->total_folder_import > 0 ) : ?>
<h2><?php esc_html_e( 'Import', 'filebird' ); ?></h2>
<div id="fbv-import-setting">
    <p>
        <?php esc_html_e( 'Import categories/folders from other plugins. We import virtual folders, your website will be safe, don\'t worry ;)', 'filebird' ); ?>
    </p>
    <table class="form-table">
        <tbody>
            <?php foreach ( $data_import->data as $data ) : ?>
            <tr class="<?php echo esc_attr( $data->counter <= 3 ? 'hidden' : '' ); ?>">
                <th scope="row">
                    <label for="">
                        <?php echo esc_html( $data->name ); ?>
                        <?php esc_html_e( ' by ', 'filebird' ); ?>
                        <?php echo esc_html( $data->author ); ?>
                    </label>
                </th>
                <td>
                    <?php if ( $data->counter > 0 ) : ?>
                    <button 
                        class="button button-primary button-large njt-fb-import njt-button-loading"
                        data-site=<?php echo esc_attr( $data->prefix ); ?> 
                        type="button"
                        data-count="<?php echo esc_attr( $data->counter ); ?>"
                        <?php
                        if ( $data->completed ) {
                            echo 'disabled';
                        }
						?>
                    >
                    <?php esc_html_e( 'Import Now', 'filebird' ); ?>
                    </button>
                    <?php endif; ?>
                    <p class="description">
                        <?php
					    echo sprintf( esc_html__( 'We found you have %1$s categories you created from %2$s plugin.', 'filebird' ), '<strong>(' . esc_html( $data->counter ) . ')</strong>', '<strong>' . esc_html( $data->name ) . '</strong>' );
						if ( $data->counter > 0 ) {
							echo sprintf( esc_html__( ' Would you like to import to %1$sFileBird%2$s?', 'filebird' ), '<strong>', '</strong>' );
						}
						?>
                    </p>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="fbv-row-breakline">
        <span class="fbv-breakline"></span>
    </div>
    <?php endif; ?>
    <h2><?php esc_html_e( 'Export', 'filebird' ); ?></h2>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="">
                        <?php esc_html_e( 'Export CSV', 'filebird' ); ?>
                    </label>
                </th>
                <td>
                    <div class="flex-item-center">
                        <button class="button button-primary button-large njt-fb-csv-export njt-button-loading"
                            type="button">
                            <?php esc_html_e( 'Export Now', 'filebird' ); ?>
                        </button>
                        <a id="njt-fb-download-csv" href="javascript:;" class="hidden"><?php esc_html_e( 'Download File', 'filebird' ); ?></a>
                    </div>
                    <p class="description">
                        <?php esc_html_e( 'The current folder structure will be exported.', 'filebird' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="">
                        <?php esc_html_e( 'Import CSV', 'filebird' ); ?>
                    </label>
                </th>
                <td>
                    <div class="flex-item-center">
                        <input type="file" accept=".csv" id="njt-fb-upload-csv" name="csv_file">
                        <button class="button button-large njt-fb-csv-import hidden njt-button-loading" type="button">
                            <?php esc_html_e( 'Import Now', 'filebird' ); ?>
                        </button>
                    </div>
                    <p class="description">
                        <?php esc_html_e( 'Choose FileBird CSV file to import.', 'filebird' ); ?><br />
                        <?php esc_html_e( '(Please check to make sure that there is no duplicated name. The current folder structure is preserved.)', 'filebird' ); ?><br />
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
</div>