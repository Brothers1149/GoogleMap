<template>
  <div class="container">
    <div class="card shadow-lg mt-5">
      <h5 class="card-header">Google Map</h5>
      <div class="card-body">
        <div class="row col-12">
          <div class="input-group mb-3">
            <gmap-autocomplete placeholder="ป้อนตำแหน่ง ปัจจุบัน" :select-first-on-enter="true" @place_changed="initMarkerStart" class="form-control"></gmap-autocomplete>
              <button type="button" @click="addLocationMarker" class="btn btn-outline-secondary">Search</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-lg mt-3 mb-3">
      <div class="card-body">
        <div class="row col-12">
          <div class="col-7">
            <gmap-map :zoom="17" :center="center" style="width: 100%; height: 500px">
              <gmap-polyline v-bind:path.sync="path" v-bind:options="{ strokeColor:'#008000'}"></gmap-polyline>
              <gmap-marker :key="index" v-for="(m, index) in locationMarkers" :icon="m.icon" :position="m.position" @click="getDirections(m)"></gmap-marker>
            </gmap-map>
          </div>
          <div class="col-5">
            <table class="table table-bordered table-fixed table-hover">
              <thead>
                <tr>
                  <th scope="col" class="text-center">list Restaurants</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in locationMarkers.filter(e=>(e.photo||'') != '')" :key="idx">
                  <th style="cursor: pointer;font-weight: normal;" @click="getDirections(item)">
                    <div>
                      <div class="row">
                        <div class="col-4 text-center">
                          <img class="img-fluid rounded" :src="getPhoto(item)" />
                        </div>
                        <div class="col-8">
                          <div class="row">
                            <div>{{item.name}}</div>
                          </div>
                          <div class="row">
                            <div class="float-left">
                              <label>{{item.rating}}</label>
                              <b-form-rating inline variant="warning" size="sm" no-border v-model="item.rating" readonly precision="2"></b-form-rating>
                              <label>({{item.user_ratings_total}})</label>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </th>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  const apiUrl = 'http://localhost:8000';
  const apiKey = 'AIzaSyCZi9MB9rv5iXekRpDfNkJilgNmyFvUhb0';
  export default {
    name: "GoogleMap",
    data() {
      return {
        center: {'lat': 13.8030424,'lng': 100.5391926},
        locationMarkers: [],
        locationStart: {},
        path:[]
      };
    },

    mounted() {
      this.locateGeoLocation();
    },

    methods: {
      initMarkerStart(x){
        this.locationStart = x;
        this.addLocationMarker();
        this.path = [];
      },
      getPhoto(x){
        if(x.photo_ref == ''){
          return x.photo;
        }
        return 'https://maps.googleapis.com/maps/api/place/photo?key='+apiKey+'&maxwidth=100&photo_reference='+x.photo_ref;
      },
      async getDirections(x) {
        let loading = this.$loading.show();
        try{
          if(((x||{}).place_id||'') == ''){
            return;
          }

          /* ========== Mapping data ========== */
          let json = {
            'locationStart':this.locationStart.place_id||'ChIJjVQLy3Kc4jARJMBXQIqIvVc', // Default Bang sue
            'locationEnd':x.place_id
          };

          /* ========== API ========== */
          let {data} = await this.$axios.post(apiUrl+'/api/search-directions',json);
          loading.hide();

          if(data.status == 'ZERO_RESULTS'){
            this.$swal({icon: 'question',title: 'ไม่พบเส้นทาง'});
            return;
          }
          
          if(data.status != 'OK'){
            this.$swal({icon: 'error',title: 'เกิดข้อผิดพลาดจากระบบ กรุณาติดต่อเจ้าหน้าที่'});
            return;
          }

          let routes = ((data||{}).routes||[])[0]||{};
          
          /* ========== Polyline ========== */
          this.path = ((routes.overview_polyline||{}).points||[]).reduce((arr,item)=>{
            return [...arr, item];
          },[]);
        }catch(ex){
          console.log(ex);
        }finally{
          loading.hide();
        }
      },
      async addLocationMarker() {
        let loading = this.$loading.show();
        try{
          /* ========== Validate ========== */
          if((this.locationStart||'') == ''){
            this.$swal({icon: 'question',title: 'กรุณาระบุข้อมูลให้ครบถ้วน'});
            return;
          }

          /* ========== Mapping data ========== */
          let location = ((this.locationStart||{}).geometry||{}).location||{};
          let lat = this.center.lat;
          let lng = this.center.lng;
          let placeId = this.locationStart.place_id;
          try{
            lat = location.lat();
            lng = location.lng();
          }catch(ex){}
          let json = {
            'latitude':lat,
            'longitude':lng
          };

          /* ========== API ========== */
          let {data} = await this.$axios.post(apiUrl+'/api/search-restaurants',json);
          

          if(data.status == 'ZERO_RESULTS'){
            this.$swal({icon: 'question',title: 'ไม่พบเส้นทาง'});
            return;
          }

          /* ========== Photo , Markers ========== */
          this.locationMarkers = (data.results||[]).map(o=>{
            return {
              'position':{'lat': o.geometry.location.lat,'lng': o.geometry.location.lng}
              ,'place_id':o.place_id
              ,'name':o.name
              ,'icon': require('@/assets/image/restaurant-80.png')
              ,'photo': require('@/assets/image/restaurant-1.png')
              , 'photo_ref': (((o.photos||[]).shift()||{}).photo_reference||'')
              ,'rating':(o.rating||0)
              ,'user_ratings_total':(o.user_ratings_total||0)
          };});

          this.locationMarkers.push({
            'position':{'lat': lat,'lng': lng}
            ,'place_id':placeId
            ,'icon': require('@/assets/image/location-1.png')
          });

          /* ========== Markers center ========== */
          this.center = {'lat':lat,'lng':lng};
        }catch(ex){
          console.log(ex);
        }finally{
          loading.hide();
        }
      },
      locateGeoLocation: function () {
        navigator.geolocation.getCurrentPosition((res) => {
          this.center = {'lat': 13.8030424,'lng': 100.5391926};
        });
        this.addLocationMarker();
      },
    },
  };
</script>
