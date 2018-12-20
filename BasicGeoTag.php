<?php
/**
 * Plugin Name:       Basic GeoTag
 * Plugin URI:        https://github.com/lostfocus/basic-geotag
 * Description:       Very basic functionalities to add geo tags to a blog post
 * Version:           1.0
 * Author:            Dominik Schwind
 * Author URI:        https://dominikschwind.com
 */

class BasicGeoTag
{
    const POST_META_LATITUDE = 'geo_latitude';
    const POST_META_LONGITUDE = 'geo_longitude';
    const POST_META_PUBLIC = 'geo_public';

    var $version = "1.0";


    public function add_meta_box($post_type, $post)
    {
        add_meta_box(
            'basic_geotag__meta_box',
            'GeoTag',
            [$this, 'render_meta_box_content'],
            $post_type,
            'normal'
        );
    }

    public function render_meta_box_content(WP_Post $post)
    {
        $lat = get_post_meta($post->ID, self::POST_META_LATITUDE, true);
        $lng = get_post_meta($post->ID, self::POST_META_LONGITUDE, true);

        wp_enqueue_script('leaflet-js', plugins_url('js/leaflet.js', __FILE__), [], '1.3.4');
        wp_enqueue_style('leaflet-css', plugins_url('css/leaflet.css', __FILE__), [], '1.3.4');
        ?>
        <table style="float:left;">
            <tr style="text-align:left;">
                <th>Latitude</th>
                <th>Longitude</th>
                <th></th>
            </tr>
            <tr>
                <td>
                    <input type="text" name="geo_latitude" id="lat" size="10" style="width:10em;" value="<?php echo $lat; ?>"/>
                </td>
                <td>
                    <input type="text" name="geo_longitude" id="lng" size="10" style="width:10em;" value="<?php echo $lng; ?>"/>&nbsp;&nbsp;&nbsp;
                </td>
                <td>
                    <input type="button" id="current_location" onclick="return false" value="Current Location" class="button"/>
                </td>
            </tr>
        </table>
        <br style="clear:both;"/>
        <div id="basicgeotagmap" style="height: 400px; width: 100%; padding: 0px; margin: 0px; position: relative; overflow: hidden;"></div>
        <script>
            function updateGeoForm(position) {
                console.log(position);
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                map.setView([lat, lng], 17);
                map.eachLayer(function (layer) {
                    layer.setOpacity(1);
                });
                setMarker(lat, lng);
                latform.value = lat;
                lngform.value = lng;
            }

            function handleGeoError(error) {
                console.log(error);
            }

            function setMarker(lat, lng) {
                window.marker = L.marker([lat, lng]);

                marker.options.draggable = true;


                marker.on("drag", function (e) {
                    var marker = e.target;
                    var markerposition = marker.getLatLng();
                    map.panTo(new L.LatLng(markerposition.lat, markerposition.lng));
                    latform.value = markerposition.lat;
                    lngform.value = markerposition.lng;
                });
                marker.on("dragend", function (e) {
                    var ll = marker.getLatLng();
                });

                marker.addTo(map);
            }

            document.addEventListener('DOMContentLoaded', function () {

                window.latform = document.getElementById('lat');
                window.lngform = document.getElementById('lng');

                var lat = latform.value;
                var lng = lngform.value;

                if (lat === '') lat = 51.5194133;
                if (lng === '') lng = -0.1291453;

                window.map = L.map('basicgeotagmap').setView([lat, lng], 8);

                <?php if($lat != '' && $lng != ''): ?>
                map.setView([lat, lng], 17);
                setMarker(lat, lng);
                <?php endif; ?>

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(window.map);

                setTimeout(function () {
                    map.invalidateSize();
                }, 0);

                var locationButton = document.getElementById('current_location');

                locationButton.onclick = function () {
                    navigator.geolocation.getCurrentPosition(updateGeoForm, handleGeoError, {enableHighAccuracy: true, maximumAge: 10000});

                    return false;
                }

            });
        </script>
        <?php
    }

    public function save_meta_box_content($post_id, $post)
    {
        if (isset($_POST['geo_latitude']) && isset($_POST['geo_longitude'])) {
            update_post_meta($post_id, self::POST_META_PUBLIC, 1);
            update_post_meta($post_id, self::POST_META_LATITUDE, round((float)trim($_POST['geo_latitude']), 5));
            update_post_meta($post_id, self::POST_META_LONGITUDE, round((float)trim($_POST['geo_longitude']), 5));
        }
    }

    public function init()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_box'], 10, 2);
        add_action('save_post', [$this, 'save_meta_box_content'], 10, 2);
    }
}

$basicgeotag = new BasicGeoTag();
$basicgeotag->init();