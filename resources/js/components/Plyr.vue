<!-- video element -->
<template>
  <vue-plyr ref="player">
    <video id="player" :crossorigin="IsCrossorigin" controls playsinline :src=mp4 :poster=image_url>
      <source :src=mp4 type="video/mp4" size="720">

      <track v-if="vtt_en" kind="captions" label="English" srclang="en" :src=vtt_en default  type="text/vtt">
      <track v-if="vtt_cn" kind="captions" label="中文" srclang="en" :src=vtt_cn  type="text/vtt">

    </video>
  </vue-plyr>
</template>

<script>
  export default {
    props : [
      'mp4',
      'vtt_en',
      'image_url',
      'vtt_cn',
      'crossorigin'
    ],
    computed: {
      IsCrossorigin () {
          if (this.crossorigin) {
              return 'crossorigin'
          }
      }
  }
  }
</script>
