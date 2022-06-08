<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$year = filter_input(INPUT_POST, 'year');
$month = filter_input(INPUT_POST, 'month');
$day = filter_input(INPUT_POST, 'day');
$day = ($day == 0) ? '' : $day;
?>
<div class="wrap">
    <h2>Purgar entradas de la base de datos</h2>
    <div id="pxe_wipe_post">
    <?php if ( '' != $year && '' != $month ) : ?>
    <?php 
            $query_post = new WP_Query(array(
                'date_query' => array(
                    array(
                        'year'  => $year,
                        'month' => $month,
                        'day' => $day
                    ),
                ),
                'posts_per_page' => -1
            ));
            if ( $query_post->have_posts() ) : 
                while ( $query_post->have_posts() ) : $query_post->the_post();
                    $posts[] = get_the_ID();
                endwhile; 
                ?>
                <input type="hidden" name="posts" id="posts_id" value="<?php echo json_encode( $posts ) ?>" />
                <p class="warning"><?php echo count( $posts ) ?> artículos para procesar.</p>
                <p class="warning">Recuerde respaldar la Base de Datos...</p>
                <div id="processing"></div>
                <p><?php submit_button( 'Procesar !', 'primary', '', FALSE, array( 'id' => 'pxe_wp_process' ) ) ?></p>
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
            $query_year1 = new WP_Query( array( 'posts_per_page' => 1, 'order' => 'ASC' ) );
            if ( $query_year1->have_posts() ) : while ( $query_year1->have_posts() ) : $query_year1->the_post();
                    $year1 = get_the_date( 'Y' );
            endwhile; endif;
            $query_year2 = new WP_Query( array( 'posts_per_page' => 1 ) );
            if ( $query_year2->have_posts() ) : while ( $query_year2->have_posts() ) : $query_year2->the_post();
                    $year2 = get_the_date( 'Y' );
            endwhile; endif;
    ?>
    <p>Se consulta si la entradas correspondientes a la fecha seleccionada ya fueron cacheadas y si es así se borra de la BD y se fija</p>
    <form method="post" action="<?php echo esc_html(admin_url('tools.php?page=pxe-wipe-posts')) ?>">
        <input type="hidden" name="page" value="pxe-wipe-posts" />
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Año</th>
                    <td>
                        <select name="year" >
                            <?php for ($year = $year1; $year <= $year2; $year++ ) : ?>
                            <option><?php echo $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mes</th>
                    <td>
                        <select name="month" >
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
                        <select name="day" >
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
