(function () {
  let cssCustomPropertiesSupport = function () {
    let temp = document.createElement('span');
    let tempBg = 'rgb(1,1,1)';
    document.body.appendChild(temp);
    temp.style.backgroundColor = 'var(--support-test)';
    temp.style.setProperty("--support-test", tempBg);
    return window.getComputedStyle(temp).backgroundColor.replace(/ /g, '') === tempBg;
  };

  let sheet = document.createElement('style');

  let Parallax = function (el) {

    if (!cssCustomPropertiesSupport()) {
      console.log("This browser is too old to support this feature.");
      return false;
    }

    this.element = el;
    this._type = this.element.tagName === "IMG" ? "image" : this.element.hasAttribute('data-parallax-background') ? "background" : "element";
    this.wrapper = document.createElement('div');
    this.loader = this._type === "image" ? this.element : { complete: true };
    this.scale = this.element.getAttribute(this._type === "background" ? "data-parallax-background" : "data-parallax");

    this.init = function () {
      let observer = new IntersectionObserver((entry, observer) => {
        if (entry[0].isIntersecting) {
          Parallax._elements.push(this);
          this.adjust();
        } else {
          let index = Parallax._elements.findIndex(el => el === this);
          if (index > -1) {Parallax._elements.splice(index, 1);}
        }
      });

      this.wrapper.className = "parallax-wrapper";
      this.element.parentNode.insertBefore(this.wrapper, this.element);
      this.wrapper.appendChild(this.element);

      this.element.style.setProperty("--parallax-scale", this._type === "background" ? this.scale * 100 + '%' : this.scale);
      this.element.style.setProperty("--parallax-transform", this._type === "element" ? this.scale * this.element.parentNode.clientHeight / -2 + "px" : 0);

      observer.observe(this.element);
    };
    this.move = {
      image: function (traveled, diff) {return -1 * traveled * diff + "px";},
      background: function (traveled) {return 100 * traveled + '%';},
      element: function (traveled, diff) {return traveled * diff - diff / 2 + "px";} };

    this.adjust = function () {
      let box = this.wrapper.getBoundingClientRect();
      let imgBox = this.element.getBoundingClientRect();
      let traveled = 1 - box.bottom / (window.innerHeight + box.height);
      let diff = this._type === "image" || this._type === "background" ? imgBox.height - box.height : this.element.parentNode.clientHeight * this.scale;
      this.element.style.setProperty("--parallax-transform", this.move[this._type](traveled, diff));
    };

    this.loader.complete ? this.init() : this.loader.addEventListener("load", () => this.init());
  };

  sheet.innerHTML = '.parallax-wrapper { height: 100%; overflow: hidden; }' +
  '[data-parallax] { transform-origin: 50% 0%; }' +
  'img[data-parallax] { transform: translateY(var(--parallax-transform)) scale(var(--parallax-scale)); }' +
  '[data-parallax]:not(img) { transform: translateY(var(--parallax-transform)); }' +
  '[data-parallax-background] { background-position-y: var(--parallax-transform); background-size: var(--parallax-scale) auto; }';
  document.head.appendChild(sheet);


  Parallax._watcher = function () {Parallax._elements.forEach(item => item.adjust());};

  Parallax._elements = new Proxy([], {
    set: function (target, property, value, receiver) {
      if (!isNaN(property) && target.length === 0) {
        window.addEventListener("scroll", Parallax._watcher);
      }

      target[property] = value;
      return true;
    },
    deleteProperty(target, prop) {
      if (prop in target) {

        if (target.length === 1) {
          window.removeEventListener("scroll", Parallax._watcher);
        }

        target.splice[(prop, 1)];
        return true;
      }
    } });


  window.Parallax = Parallax;
})();