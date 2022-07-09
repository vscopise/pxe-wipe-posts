<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

//$year = filter_input(INPUT_POST, 'year');
//$year = sprintf("%04d", filter_input(INPUT_POST, 'year'));
$year = filter_input(INPUT_POST, 'year') == '' ? '' : sprintf("%04d", filter_input(INPUT_POST, 'year'));
//$month = filter_input(INPUT_POST, 'month');
$month = sprintf("%02d", filter_input(INPUT_POST, 'month'));
//$day = (filter_input(INPUT_POST, 'day') == 0) ? '' : $day;
$day = sprintf("%02d", filter_input(INPUT_POST, 'day'));
$remote_url = filter_input(INPUT_POST, 'remote_url');

?>
<div class="wrap">
    <h2>Purgar entradas de la base de datos</h2>
    <div id="pxe_wipe_post">
        <?php if ('' != $year && '' != $month) : ?>
            <?php
            if ('00' == $day) {
                // mes completo
                $after  = date('Y-m-d', strtotime("{$year}-{$month}-01 - 1 day")) . "T23:59:59";
                $before = date('Y-m-d', strtotime("{$year}-{$month}-01 + 1 month")) . "T00:00:00";
            } else {
                // un solo día
                $after  = date('Y-m-d', strtotime("{$year}-{$month}-{$day} - 1 day")) . "T23:59:59";
                $before = date('Y-m-d', strtotime("{$year}-{$month}-{$day} + 1 day")) . "T00:00:00";
            }
            $have_posts = true;
            $page = 1;
            $posts = array();
            $data_posts = array();
            $body_content = array();
            while ($have_posts) {
                $url = $remote_url . "/wp-json/wp/v2/posts?after={$after}&before={$before}&page={$page}";
                $response = wp_remote_get($url, array('timeout' => 120));
                if (is_array($response) && !is_wp_error($response)) {
                    $body = wp_remote_retrieve_body($response);
                    $body_content = json_decode($body, true);
                    $invalid_page_number = false;
                    if (array_key_exists('code', $body_content)) {
                        $invalid_page_number = 'rest_post_invalid_page_number' == $body_content['code'];
                    }
                    if (0 == sizeof($body_content) || $invalid_page_number) {
                        $have_posts = false;
                    } else {
                        $posts = array_merge($posts, array_column($body_content, 'id'));
                        $data_posts = array_merge($data_posts, $body_content);
                    }
                }
                $page++;
            }
            update_option('pxe_wipe_posts_options', array(
                'remote_url' => $remote_url,
                'year' => $year,
                'data_posts' => $data_posts,
            ));


            if (0 < sizeof($posts)) :
            ?>
                <input type="hidden" name="posts" id="posts_id" value="<?php echo json_encode($posts) ?>" />
                <p class="warning"><?php echo count($posts) ?> artículos para procesar.</p>
                <p class="warning">Recuerde respaldar la Base de Datos <em>(los artículos procesados serán eliminados de la BD)</em>...</p>
                <div id="processing"></div>
                <p><?php submit_button('Procesar !', 'primary', '', FALSE, array('id' => 'pxe_wp_process')) ?></p>
            <?php
            else :
            ?>
                <p>
                    No hay artículos con este criterio
                </p>
                <p>
                    <a href="<?php echo esc_html(admin_url('tools.php?page=pxe-wipe-posts')) ?>">Volver</a>
                </p>
            <?php
            endif;
            ?>
    </div>
<?php else : ?>
    <?php
            $pxe_wipe_posts_options = get_option('pxe_wipe_posts_options', array('remote_url' => '', 'year' => ''));
            $remote_url = $pxe_wipe_posts_options['remote_url'];
            $year = $pxe_wipe_posts_options['year'];
    ?>
    <p>Se consulta si la entradas correspondientes a la fecha seleccionada ya fueron cacheadas y si es así se borra de la BD y se fija</p>
    <form method="post" action="<?php echo esc_html(admin_url('tools.php?page=pxe-wipe-posts')) ?>">
        <input type="hidden" name="page" value="pxe-wipe-posts" />
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Remote url</th>
                    <td><input type="text" id="pxe_remote_url" name="remote_url" value="<?php echo $remote_url ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Año</th>
                    <td><input type="text" id="pxe_year" name="year" value="<?php echo $year ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Mes</th>
                    <td>
                        <select name="month">
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Setiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Día</th>
                    <td>
                        <select name="day">
                            <option value="0">todos</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                            <option value="13">13</option>
                            <option value="14">14</option>
                            <option value="15">15</option>
                            <option value="16">16</option>
                            <option value="17">17</option>
                            <option value="18">19</option>
                            <option value="19">19</option>
                            <option value="20">20</option>
                            <option value="21">21</option>
                            <option value="22">22</option>
                            <option value="23">23</option>
                            <option value="24">24</option>
                            <option value="25">25</option>
                            <option value="26">26</option>
                            <option value="27">27</option>
                            <option value="28">28</option>
                            <option value="29">29</option>
                            <option value="30">30</option>
                            <option value="31">31</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php submit_button('Consultar') ?></td>
                </tr>
            </tbody>
        </table>
    </form>
<?php endif; ?>
</div>