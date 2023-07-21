<template>
  <div class="container">
    <div class="card shadow-lg mt-5">
      <h5 class="card-header">Google Map</h5>
      <div class="card-body">
        <div class="row col-12">
          <div class="col-10">
            <label class="form-label">Existing place</label>
            <gmap-autocomplete placeholder="ป้อนตำแหน่ง ปัจจุบัน" :select-first-on-enter="true" @place_changed="initMarkerStart" class="form-control"></gmap-autocomplete>
          </div>
          <div class="col-2 d-flex align-self-end">
            <div class="text-center">
              <button type="button" @click="addLocationMarker" class="btn btn-primary">Search</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-lg mt-3 mb-3">
      <div class="card-body">
        <div class="row col-12">
          <div class="col-8">
            <gmap-map :zoom="17" :center="center" style="width: 100%; height: 500px">
              <gmap-polyline v-bind:path.sync="path" v-bind:options="{ strokeColor:'#008000'}"></gmap-polyline>
              <gmap-marker :key="index" v-for="(m, index) in locationMarkers" :position="m.position" @click="getDirections(m)"></gmap-marker>
            </gmap-map>
          </div>
          <div class="col-4">
            <table class="table table-bordered table-fixed table-hover">
              <thead>
                <tr>
                  <th scope="col" class="text-center">list Restaurants</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in locationMarkers" :key="idx">
                  <th style="cursor: pointer;" @click="getDirections(item)">{{item.name}}</th>
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
  export default {
    name: "GoogleMap",
    data() {
      return {
        center: {'lat': 13.828253,'lng': 100.5284507},
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
        
      },
      async getDirections(x) {
        let loading = this.$loading.show();
        try{
          if(((x||{}).place_id||'') == ''){
            return;
          }

          /* ========== Mapping data ========== */
          let json = {
            'locationStart':this.locationStart.place_id||'ChIJX5dpCoGb4jARUE_iXbIAAQM', // Default Bang sue
            'locationEnd':x.place_id
          };

          /* ========== API ========== */
          let {data} = await this.$axios.post('http://localhost:8000/api/search-directions',json);
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
          try{
            lat = location.lat();
            lng = location.lng();
          }catch(ex){}
          let json = {
            'latitude':lat,
            'longitude':lng
          };

          /* ========== API ========== */
          let {data} = await this.$axios.post('http://localhost:8000/api/search-restaurants',json);
          loading.hide();

          if(data.status == 'ZERO_RESULTS'){
            this.$swal({icon: 'question',title: 'ไม่พบเส้นทาง'});
            return;
          }

          this.locationMarkers = (data.results||[]).map(o=>{
            return {'position':{'lat': o.geometry.location.lat,'lng': o.geometry.location.lng},'place_id':o.place_id,'name':o.name};
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
          this.center = {'lat': 13.828253,'lng': 100.5284507};
        });
        this.addLocationMarker();
      },
    },
  };
</script>
