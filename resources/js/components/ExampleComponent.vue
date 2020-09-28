<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="w-25 mt-4 mb-4">
          <img
            src="https://www.betterrx.com/hubfs/Betterrx_May2019/Images/betterRX_logo_outlined.svg"
            alt="BetterRX"
            id="logo"
            data-height-percentage="54"
            data-actual-width="300"
            data-actual-height="122"
          />
        </div>

        <div class="card card-default mb-4">
          <div class="card-header">Image upload</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <input
                  type="file"
                  v-on:change="onImageChange"
                  class="form-control"
                />
              </div>
              <div class="col-md-3">
                <button class="btn btn-success btn-block" @click="uploadImage">
                  Upload Image
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="card card-default">
          <div class="card-header">Uploaded images</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-4" v-for="item in images">
                <div class="thumbnail">
                  <a v-bind:href="item.url">
                    <img v-bind:src="item.url" style="width: 100%" />
                    <div class="caption">
                      <p>
                        Uploaded on:<br />{{
                          new Date(item.time * 1000).toLocaleDateString("en-US")
                        }}
                        -
                        {{
                          new Date(item.time * 1000).toLocaleTimeString("en-US")
                        }}
                      </p>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data: function () {
    return {
      image: "",
      images: [],
    };
  },
  mounted() {
    this.loadImages();
  },
  methods: {
    loadImages() {
      axios
        .get("images")
        .then((response) => {
          this.images = response.data.images;
        })
        .catch((error) => {
          console.log(error);
        });
    },
    onImageChange(e) {
      let files = e.target.files || e.dataTransfer.files;
      if (!files.length) return;
      this.image = files[0];
    },
    uploadImage() {
      const fd = new FormData();
      fd.append("image", this.image, this.image.name);
      axios
        .post("/upload", fd)
        .then((response) => {
          if (response.data.success) {
            alert(response.data.success);
            this.loadImages();
          }
          if (response.data.error) {
            alert(response.data.error.image);
          }
        })
        .catch((error) => {
          alert(error);
        });
    },
  },
};
</script>
