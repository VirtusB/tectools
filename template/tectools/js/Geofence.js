/**
 * Denne klasse indeholder metoder som kan bruges til at lave et Geofence
 */
class Geofence {
    /**
     * Brugerens latitude
     * @type {number}
     * @name userLat
     */
    static userLat;

    /**
     * Brugerens longitude
     * @type {number}
     * @name userLong
     */
    static userLong;

    /**
     * Henter brugerens koordinater via Geolocation API i browseren
     */
    static getUserLocation(success, error) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                this.userLat = position.coords.latitude;
                this.userLong = position.coords.longitude;
                console.log('User location set');

                if (typeof success === 'function') {
                    success(position);
                }
            }, (err) => {
                if (typeof error === 'function') {
                    error(err);
                }
            });
        }
    }

    /**
     * Denne metode tjekker om brugerens koordinater er for langt væk i forhold til de koordinater der gives til metoden
     * @param {number} locationLat Latitude af stedet der skal sammenlignes med brugeren latitude
     * @param {number} locationLong Longitude af stedet der skal sammenlignes med brugeren longitude
     * @param {number} maxDistance Maks distance, i meter, der må være mellem brugerens koordinater og koordinaterne der gives til metoden
     * @return {boolean}
     */
    static isUserWithinRadius(locationLat, locationLong, maxDistance) {
        if (this.userLat === undefined || this.userLong === undefined) {
            console.log('User location missing');
            return false;
        }

        let dLat = (this.userLat - locationLat) * Math.PI / 180;
        let dLon = (this.userLong - locationLong) * Math.PI / 180;
        let a = 0.5 - Math.cos(dLat) / 2 + Math.cos(locationLat * Math.PI / 180) * Math.cos(this.userLat * Math.PI / 180) * (1 - Math.cos(dLon)) / 2;
        let d = Math.round(6371000 * 2 * Math.asin(Math.sqrt(a)));

        console.log('Distance: ' + d + ' m');

        return d < maxDistance;
    }

    /**
     * Køre isUserWithinRadius for hver lokation angivet i "arr" argumentet
     *
     * Hvis ikke brugeren er i nærheden af en af lokationerne, returnere metoden false
     * @param arr
     * @param maxDistance
     * @return {boolean}
     */
    static oneWithinRadius(arr, maxDistance) {
        return arr.some(location => this.isUserWithinRadius(location.lat, location.long, maxDistance));
    }
}