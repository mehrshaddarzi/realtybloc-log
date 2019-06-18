<div class="postbox-container rbl-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside" style="margin-bottom: 0px;">
                    <table width="100%" class="widefat table-stats" style="border:0;">
                        <tr>
                            <td><?php _e( 'User ID', 'realty-bloc-log' ); ?></td>
                            <td><?php _e( 'Name', 'realty-bloc-log' ); ?></td>
                            <td><?php _e( 'Email', 'realty-bloc-log' ); ?></td>
                            <td><?php _e( 'User login', 'realty-bloc-log' ); ?></td>
                            <td><?php _e( 'User Website', 'realty-bloc-log' ); ?></td>
                            <td><?php _e( 'Number of All Event', 'realty-bloc-log' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $user_id; ?></td>
                            <td><?php echo $user_name; ?></td>
                            <td><?php echo $user['user_email']; ?></td>
                            <td><?php echo $user['user_login']; ?></td>
                            <td><?php echo( $user['user_url'] == "" ? '_' : $user['user_url'] ); ?></td>
                            <td class="rbl-text-danger"><?php echo number_format_i18n( $number_of_event ) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="postbox-container rbl-postbox-full" style="margin-top: -10px !important;">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside" style="margin-bottom: 0;">
                    <script>
                        var rbl_chart_history_log = '<?php echo json_encode( $chart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK ); ?>';
                    </script>
                    <canvas id="rbl-user-history-canvas" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="postbox-container rbl-postbox-full" style="margin-top: -10px !important;">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside" style="margin-bottom: 0px;">
                    <table width="100%" class="widefat table-stats" style="border:0;">
                        <tr>
                            <td></td>
							<?php
							foreach ( $chart['label'] as $key => $val ) {
								echo '<td style="text-align: center;">' . $val . '</td>';
							}
							?>
                        </tr>
                        <tr>
                            <td><?php _e( "Chart Total", "realty-bloc-log" ); ?></td>
							<?php
							foreach ( $chart['label'] as $key => $val ) {
								$sum = 0;
								foreach ( $chart['data'][ $key ] as $number ) {
									$sum += $number;
								}
								echo '<td style="text-align: center;">' . number_format_i18n( $sum ) . '</td>';
							}
							?>
                        </tr>
                        <tr>
                            <td><?php _e( "Total All Time", "realty-bloc-log" ); ?></td>
		                    <?php
		                    foreach ( $chart['label'] as $key => $val ) {
			                    $sum = 0;
			                    foreach ( $user_event_total[ $key ] as $number ) {
				                    $sum += $number;
			                    }
			                    echo '<td style="text-align: center;">' . number_format_i18n( $sum ) . '</td>';
		                    }
		                    ?>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>