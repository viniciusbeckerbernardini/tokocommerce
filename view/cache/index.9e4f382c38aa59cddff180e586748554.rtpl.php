<?php if(!class_exists('Rain\Tpl')){exit;}?>   <div class="site-branding-area">
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <div class="logo">
                    <h1><a href="hahaha"><img src="/res/site/img/logo.png"></a></h1>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="shopping-item">
                    <a href="carrinho.html">Carrinho - <span class="cart-amunt">R$100</span> <i class="fa fa-shopping-cart"></i> <span class="product-count">5</span></a>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End site branding area -->

<div class="mainmenu-area">
    <div class="container">
        <div class="row">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div> 
            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li class="active"><a href="/">Home</a></li>
                    <li><a href="/cart">Carrinho</a></li>
                </ul>
            </div>  
        </div>
    </div>
</div> <!-- End mainmenu area -->

<div class="slider-area">
 <!-- Slider -->
 <div class="block-slider block-slider4">
    <ul class="" id="bxslider-home4">
     <li>
      <img src="/res/site/img/h4-slide.png" alt="Slide">
      <div class="caption-group">
       <h2 class="caption title">
        iPhone <span class="primary">6 <strong>Plus</strong></span>
    </h2>
    <h4 class="caption subtitle">Dual SIM</h4>
    <a class="caption button-radius" href="#"><span class="icon"></span>Comprar</a>
</div>
</li>
<li><img src="/res/site/img/h4-slide2.png" alt="Slide">
  <div class="caption-group">
   <h2 class="caption title">
    by one, get one <span class="primary">50% <strong>off</strong></span>
</h2>
<h4 class="caption subtitle">school supplies & backpacks.*</h4>
<a class="caption button-radius" href="#"><span class="icon"></span>Comprar</a>
</div>
</li>
<li><img src="/res/site/img/h4-slide3.png" alt="Slide">
  <div class="caption-group">
   <h2 class="caption title">
    Apple <span class="primary">Store <strong>Ipod</strong></span>
</h2>
<h4 class="caption subtitle">Select Item</h4>
<a class="caption button-radius" href="#"><span class="icon"></span>Comprar</a>
</div>
</li>
<li><img src="/res/site/img/h4-slide4.png" alt="Slide">
  <div class="caption-group">
    <h2 class="caption title">
        Apple <span class="primary">Store <strong>Ipod</strong></span>
    </h2>
    <h4 class="caption subtitle">& Phone</h4>
    <a class="caption button-radius" href="#"><span class="icon"></span>Comprar</a>
</div>
</li>
</ul>
</div>
<!-- ./Slider -->
</div> <!-- End slider area -->

<div class="promo-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="single-promo promo1">
                    <i class="fa fa-refresh"></i>
                    <p>1 ano de garantia</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-promo promo2">
                    <i class="fa fa-truck"></i>
                    <p>Frete grátis</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-promo promo3">
                    <i class="fa fa-lock"></i>
                    <p>Pagamento seguro</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-promo promo4">
                    <i class="fa fa-gift"></i>
                    <p>Novos produtos</p>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End promo area -->

<div class="maincontent-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="latest-product">
                    <h2 class="section-title">Produtos</h2>
                    <div class="product-carousel">
                        <?php $counter1=-1;  if( isset($products) && ( is_array($products) || $products instanceof Traversable ) && sizeof($products) ) foreach( $products as $key1 => $value1 ){ $counter1++; ?>
                        <div class="single-product">
                            <div class="product-f-image">
                                <img src="<?php echo htmlspecialchars( $value1["desphoto"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" alt="">
                                <div class="product-hover">
                                    <a href="#" class="add-to-cart-link"><i class="fa fa-shopping-cart"></i>Comprar</a>
                                    <a href="#" class="view-details-link"><i class="fa fa-link"></i>Ver mais</a>
                                </div>
                            </div>

                            <h2><a href="#"><?php echo htmlspecialchars( $value1["desproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?></a></h2>
                            <div class="product-carousel-price">
                                <ins>R$<?php echo formatPrice($value1["vlprice"]); ?></ins>
                            </div> 
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div> <!-- End main content area -->

<div class="brands-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="brand-wrapper">
                    <div class="brand-list">
                        <img src="/res/site/img/brand1.png" alt="">
                        <img src="/res/site/img/brand2.png" alt="">
                        <img src="/res/site/img/brand3.png" alt="">
                        <img src="/res/site/img/brand4.png" alt="">
                        <img src="/res/site/img/brand5.png" alt="">
                        <img src="/res/site/img/brand6.png" alt="">
                        <img src="/res/site/img/brand1.png" alt="">
                        <img src="/res/site/img/brand2.png" alt="">                            
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End brands area -->

<div class="footer-top-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="footer-about-us">
                    <h2>Hcode Store</h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Perferendis sunt id doloribus vero quam laborum quas alias dolores blanditiis iusto consequatur, modi aliquid eveniet eligendi iure eaque ipsam iste, pariatur omnis sint! Suscipit, debitis, quisquam. Laborum commodi veritatis magni at?</p>
                    <div class="footer-social">
                        <a href="https://www.facebook.com/hcodebr" target="_blank"><i class="fa fa-facebook"></i></a>
                        <a href="https://twitter.com/hcodebr" target="_blank"><i class="fa fa-twitter"></i></a>
                        <a href="https://www.youtube.com/channel/UCjWENuSH2gX55-y7QSZiWxA" target="_blank"><i class="fa fa-youtube"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="footer-menu">
                    <h2 class="footer-wid-title">Navegação </h2>
                    <ul>
                        <li><a href="#">Minha Conta</a></li>
                        <li><a href="#">Meus Pedidos</a></li>
                        <li><a href="#">Lista de Desejos</a></li>
                    </ul>                        
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="footer-menu">
                    <h2 class="footer-wid-title">Categorias</h2>
                    <ul>
                        <li><a href="#">Categoria Um</a></li>
                        <li><a href="#">Categoria Dois</a></li>
                        <li><a href="#">Categoria Três</a></li>
                        <li><a href="#">Categoria Quarto</a></li>
                        <li><a href="#">Categoria Cinco</a></li>
                    </ul>                        
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="footer-newsletter">
                    <h2 class="footer-wid-title">Newsletter</h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Perferendis sunt id doloribus!</p>
                    <div class="newsletter-form">
                        <form action="#">
                            <input type="email" placeholder="Type your email">
                            <input type="submit" value="Subscribe">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End footer top area -->

<div class="footer-bottom-area">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="copyright">
                    <p>&copy; 2017 Hcode Treinamentos. <a href="http://www.hcode.com.br" target="_blank">hcode.com.br</a></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="footer-card-icon">
                    <i class="fa fa-cc-discover"></i>
                    <i class="fa fa-cc-mastercard"></i>
                    <i class="fa fa-cc-paypal"></i>
                    <i class="fa fa-cc-visa"></i>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End footer bottom area -->

<!-- Latest jQuery form server -->
<script src="https://code.jquery.com/jquery.min.js"></script>

<!-- Bootstrap JS form CDN -->
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

<!-- jQuery sticky menu -->
<script src="/res/site/js/owl.carousel.min.js"></script>
<script src="/res/site/js/jquery.sticky.js"></script>

<!-- jQuery easing -->
<script src="/res/site/js/jquery.easing.1.3.min.js"></script>

<!-- Main Script -->
<script src="/res/site/js/main.js"></script>

<!-- Slider -->
<script type="text/javascript" src="/res/site/js/bxslider.min.js"></script>
<script type="text/javascript" src="/res/site/js/script.slider.js"></script>
</body>
</html>