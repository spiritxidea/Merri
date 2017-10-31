<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>

<div class="card-box">
    <h2>System Report</h2>
    
    <div class="row">
        <div class="col-xs-12">
            
            <?php foreach ( get_system_report_data() as $section_name => $section_report ) : ?>
                
                <table class="table" style="margin-bottom: 30px;">
                    <thead>
                    <tr>
                        <th colspan="2"><?php esc_html_e( $section_name ); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    
                    <?php foreach ( $section_report as $name => $value ) : ?>
                        
                        <tr>
                            <td style="width: 50%;"><?php echo $name; ?></td>
                            <td style="width: 50%;"><?php echo $value; ?></td>
                        </tr>
                    
                    <?php endforeach; ?>
                
                </table>
            
            <?php endforeach; ?>
        
        </div>
    </div>
    
    <div class="row">
        <div class="col-xs-12">
            <form method="post">
                <?php wp_nonce_field( 'pys_download_system_report_nonce' ); ?>
                <input type="hidden" name="pys_action" value="download_system_report"/>
                <button type="submit" class="btn btn-lg btn-primary btn-custom">Download System Report</button>
            </form>
        </div>
    </div>

</div>
