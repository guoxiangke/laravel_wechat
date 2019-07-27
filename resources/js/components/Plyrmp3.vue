<!-- audio element -->
<template>
  <div class="audio">
    <audio :src=mp3 autoplay controls controlsList="nodownload" >
      亲 您的浏览器不支持html5的audio标签
    </audio>
  </div>
</template>

<script>
  export default {
    props : [
      'mp3',
    ],
  }
</script>
