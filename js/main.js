$(function () {
  const $jsHamburgerMenu = $('.js-hamburgerMenu');
  const $jsNavMenu = $('.js-toggleNavMenuTarget');
  const $jsDragDrop = $('.js-dragDropArea');
  const $jsInputFile = $('.js-fileInput');
  const $jsShowMsg = $('.js-showMsg');
  const $favos = $('.js-favos') || null;
  const favosUploadId = $favos.data('upload-id') || null;
  const $favosCount = $('.js-favosCount') || null;

  //セッションスライド表示させる
  (function showSessionMessage () {
    const msg = $jsShowMsg.text();
    //空白文字を置換したあとの文字数が1文字以上なら
    if (msg.replace(/^[\s　]+|[\s　]+$/, '').length) {
      // メッセージを表示
      $jsShowMsg.slideToggle('slow');
      setTimeout(() => {
        $jsShowMsg.slideToggle('slow');
      }, 4000);
    }
  }());

  // ハンバーガーメニュー処理
  $jsHamburgerMenu.on('click', function () {
    $(this).toggleClass('is-active');
    $jsNavMenu.toggleClass('is-active');
  })

  //画像表示処理
  $jsDragDrop.on('dragover', function (e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', 'dashed');
  });
  $jsDragDrop.on('dragleave', function (e) {
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', 'none');
  });
  $jsInputFile.on('change', function (e) {
    $jsDragDrop.css('border', 'none');
    const file = this.files[0];
    const $img = $(this).siblings('.js-prevImg');
    const fileReader = new FileReader;
    fileReader.onload = function (event) {
      $img.attr('src', event.target.result).show();
    };
    //画像読み込み
    fileReader.readAsDataURL(file);
  });

  // 画像がアップロードされたら次のドラッグドロップエリアを表示
  $('.js-uploadRegist__fileInput').find('input[type="file"]').on('change', function () {
    const $jsFileInputNotFirst = $('.js-uploadRegist__fileInput').find('input[type="file"]:not([name="pic1"])').parent();
    $jsFileInputNotFirst.hide();
    $jsFileInputNotFirst.fadeIn(1500);
  });


  //submitで送信した時にサイトの最上部に遷移させないようにする
  $('form').on('submit', function () {
    const body = window.document.body;
    const html = window.document.documentElement;
    const scrollTop = body.scrollTop || html.scrollTop;

    //id="get_body_scroll_px"のvalueに自動的にスクロールが何pxか表示されるよう指定する
    $('.js-getBodyScrollPx').val(scrollTop);
  });

  // 数値の０はfalseと判定されてしまう。投稿IDが０の場合もあるので、０もtrueとする場合にはundefinedとnullを判定する
  if (favosUploadId !== undefined && favosUploadId !== null) {
    $favos.on('click', function () {
      // このときのthisは$favos（アイコンのDOM）
      const $that = $(this);
      // ajax通信を行う
      $.ajax({
        type: 'post',
        url: 'ajaxFavos.php',
        data: {
          uploadId: favosUploadId,
        },
        dataType: 'json'
      }).done(function (data) {
        const count = data.count;
        const favosFlg = data.favosFlg;
        $that.toggleClass('is-favos', favosFlg);
        if (count > 0) {
          $favosCount.text(count);
        } else {
          $favosCount.text('');
        }
      }).fail(function () {
      });
    });
  }

});
