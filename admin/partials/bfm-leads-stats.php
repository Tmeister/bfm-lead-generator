<?php
/**
 * Define vars
 */
$datas = array();
$seen  = array();
$query = array( 'data_type' => 'any', 'limit' => -1 );

/* Select a specific form */
if( 'all' != $show ) {
	$query['form_id'] = intval( $show );
}

switch( $period ) {

	case 'today':
		$timeframe = strtotime( 'today' );
	break;

	case 'this_week':

		$timeframe = array(
			'from' => strtotime( 'last monday', time() ),
			'to'   => strtotime( 'next sunday', time() )
		);

	break;

	case 'last_week':

		$timeframe = array(
			'from' => strtotime( 'last monday -1 week' ),
			'to'   => strtotime( 'next sunday -1 week' )
		);

	break;

	case 'this_month':

		$timeframe = array(
			'from' => strtotime( 'first day of this month', time() ),
			'to'   => strtotime( 'last day of this month', time() )
		);

	break;

	case 'last_month':

		$timeframe = array(
			'from' => strtotime( 'first day of last month', time() ),
			'to'   => strtotime( 'last day of last month', time() )
		);

	break;

	case 'this_quarter':

		$quarters = array( 1, 4, 7, 10 );
		$month    = intval( date( 'm' ) );

		if( in_array( $month, $quarters ) ) {
			$current = date( 'Y-m-d', time() );
		} else {

			/* Get first month of this quarter */
			while( !in_array( $month, $quarters) ) {
				$month = $month-1;
			}

			$current = date( 'Y' ) . '-' . $month . '-' . '01';

		}

		$current = strtotime( $current );

		$timeframe = array(
			'from' => strtotime( 'first day of this month', $current ),
			'to'   => strtotime( 'last day of this month', strtotime( '+2 months', $current ) )
		);

	break;

	case 'last_quarter':

		$quarters = array( 1, 4, 7, 10 );
		$month    = intval( date( 'm' ) ) - 3;
		$rewind   = false;

		if( in_array( $month, $quarters ) ) {
			$current = date( 'Y-m-d', time() );
		} else {

			/* Get first month of this quarter */
			while( !in_array( $month, $quarters) ) {

				$month = $month-1;

				/* Rewind to last year after we passed January */
				if( 0 === $month )
					$month = 12;
			}

			$current = date( 'Y' ) . '-' . $month . '-' . '01';

		}

		/* Set the theorical current date */
		$current = false === $rewind ? strtotime( $current ) : strtotime( '-1 year', $current );

		$timeframe = array(
			'from' => strtotime( 'first day of this month', $current ),
			'to'   => strtotime( 'last day of this month', strtotime( '+2 months', $current ) )
		);

	break;

	case 'this_year':

		$timeframe = array(
			'from' => strtotime( 'first day of January', time() ),
			'to'   => strtotime( 'last day of December', time() )
		);

	break;

	case 'last_year':

		$timeframe = array(
			'from' => strtotime( 'first day of January last year', time() ),
			'to'   => strtotime( 'last day of December last year', time() )
		);

	break;

}

/* Set the period */
$query['period'] = $timeframe;

/* Get the datas */
$datas = $this->db->get_datas( $query, 'OBJECT' );

?>
<div class="postbox">

	<div class="inside-no-padding">

		<table class="form-table" id="bfm-stats-general" data-timeframe="<?php echo htmlspecialchars( serialize( $timeframe ) ); ?>" data-form="<?php echo $show; ?>" data-period="<?php echo $period; ?>">
			<thead>
				<tr>
					<th class="row-title"><?php _e( 'Form Type', 'bfm' ); ?></th>
					<th><?php _e( 'Impressions', 'bfm' ); ?></th>
					<th><?php _e( 'Conversions', 'bfm' ); ?></th>
					<th><?php _e( '% Conversion', 'bfm' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if( is_array( $datas ) ) {

					foreach( $datas as $key => $data ) {

						if( in_array( $data->form_id, $seen ) )
							continue;

						/* Mark this form as parsed */
						array_push( $seen, $data->form_id );

						$impressions  = $this->db->get_datas( array( 'form_id' => $data->form_id, 'data_type' => 'impression', 'limit' => -1, 'period' => $timeframe ), 'ARRAY_A' );
						$conversions  = $this->db->get_datas( array( 'form_id' => $data->form_id, 'data_type' => 'conversion', 'limit' => -1, 'period' => $timeframe ), 'ARRAY_A' );
						$rate         = ( 0 === count( $conversions ) || 0 === count( $impressions ) ) ? 0 : ( 100 * count( $conversions ) ) / count( $impressions );

						/* Increment the global vars */
						$total_impressions = $total_impressions + count( $impressions );
						$total_conversions = $total_conversions + count( $conversions );

						$form_name = '';

						switch ($data->form_id) {
							case '1':
								$form_name = '4 Steps Form';
								break;

							case '2':
								$form_name = '3 Steps Form';
								break;
						}
						?>

						<tr valign="top">
							<td scope="row"><?php echo $form_name ?></td>
							<td><?php echo number_format( count( $impressions ), 0 ); ?></td>
							<td><?php echo number_format( count( $conversions ), 0 ); ?></td>
							<td><?php echo number_format( $rate, 2 ); ?>%</td>
						</tr>

					<?php }

				}
				?>
			</tbody>
		</table>
	</div> <!-- .inside -->
</div> <!-- .postbox -->