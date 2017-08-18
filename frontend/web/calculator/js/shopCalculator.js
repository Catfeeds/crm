$(function() {
	//车险逻辑
	var prepayment = $('#prepayment'), //首付
		monthPay = $('#monthPay'), //月供
		interest = $('#interest'), //利息
		sum_price = $('#sum_price'), //总价

		fundamental = $('#fundamental'), //基本费用
		purchaseTax = $('#purchaseTax'), //购置税
		consumption = $('#consumption'), //消费税
		registration = $('#registration'), //上牌费用
		usageTax = $('#usageTax'), //车船税
		trafficInsurance = $('#trafficInsurance'), //交强险

		commercial = $('#commercial'), // 商业保险
		thirdInsurance = $('#thirdInsurance'), // 第三者责任险
		damageInsurance = $('#damageInsurance'), // 车辆损失险
		stolenInsurance = $('#stolenInsurance'), // 全车盗抢险
		glassInsurance = $('#glassInsurance'), // 玻璃单独破碎险
		combustInsurance = $('#combustInsurance'), // 自燃损失险
		noDeductibleInsurance = $('#noDeductibleInsurance'), // 不计免赔特约险
		noLiabilityInsurance = $('#noLiabilityInsurance'), // 无过责任险
		passengerInsurance = $('#passengerInsurance'), // 车上人员责任险
		carBodyInsurance = $('#carBodyInsurance'); // 车身划痕险
		
	var loanYears = $('#loanYears'),
		carReferencePrice = $('#carReferencePrice'), //车城价
		changePrice = $('#changePrice'),
		carReferencePriceChange = $('#carReferencePriceChange'),
		payFunction = $('#payFunction'),      //付款方式
		changeoff = true,
		
		refundPrice = $('#refundPrice'),
		referenceInterest = $('#referenceInterest'),
		referenceInterestChange = $('#referenceInterestChange'),
		refundoff = true;
		//商业险选择
	var business_cost = $('#business_cost');
	
	var newCarCost = new CarPurchaseCost;
	//计算数据配置
	var carCostParam = {
        reSetCustom : true,
        //购车价格
        carPrice: 0,
        //首付自定义
        prepaymentCustom: 0,
        //首付比例
        prepaymentPercent: 1,
        //还款年限
        loanYears:1,
        //自定义上牌费用
        licenseTaxCustom: 0,
        //自定义车船使用税
        usageTaxCustom: 0,
        //排量
        displacement: 1.6,
        //座位数
        seatCount: 5,
        //是否进口车
        isImport: 0,
        //第三者责任险 赔付额度
        thirdInsureClaim: 100000,
        //自定义车上人员责任险
        passengerInsureCustom: 0,
        //车身划痕险 赔付额度
        carBodyInsureClaim: 5000,
        //是否勾选
        CommInsureCheck: {
            //第三者责任险
            thirdCheck: true,
            //车辆损失险
            damageCheck: true,
            //全车盗抢险
            stolenCheck: true,
            //玻璃单独破碎险
            glassCheck: true,
            //自燃损失险
            combustCheck: true,
            //不计免赔特约险
            noDeductibleCheck: true,
            //无过责任险
            noLiabilityCheck: true,
            //车上人员责任险
            passengerCheck: true,
            //车身划痕险
            carBodyCheck: true
        }
    }
	
	
	
	
	//选择更新
	$('body').on('tap','.mui-poppicker-btn-ok',function(){
		setTimeout(refreshChange,500)
	})
	
	changePrice.on('tap',function(){
		if(changeoff){
			carReferencePrice.parent().hide();
			carReferencePriceChange.show();
			carReferencePriceChange.val(carReferencePrice.text());
			changeoff = false;
		}else{
			carReferencePrice.text(carReferencePriceChange.val());
			carReferencePriceChange.hide();
			carReferencePrice.parent().show();
			refreshChange();
			changeoff =true;
		}
		
	});
	carReferencePriceChange.on('blur',function(){
		carReferencePrice.text(carReferencePriceChange.val());
		$(this).hide();
		carReferencePrice.parent().show();
		refreshChange();
		changeoff =true;
	})
	//还款周期
	refundPrice.on('tap',function(){
		if(refundoff){
			referenceInterest.parent().hide();
			referenceInterestChange.show();
			referenceInterestChange.val(referenceInterest.text());
			refundoff = false;
		}else{
			referenceInterest.text(referenceInterestChange.val());
			referenceInterestChange.hide();
			referenceInterest.parent().show();
			refreshChange();
			refundoff =true;
		}
		
	});
	referenceInterestChange.on('blur',function(){
		referenceInterest.text(referenceInterestChange.val());
		$(this).hide();
		referenceInterest.parent().show();
		refreshChange();
		refundoff =true;
	})


	business_cost.on('tap', '.mui-icon-checkmarkempty', function() {
		var changePace = $(this).parent().children('.pay-num').children('span');
		if($(this).hasClass('active')){
			$(this).removeClass('active');
			changePace.attr('dataoff',false);
			changePace.text(0);
		} else {
			$(this).addClass('active');
			changePace.text(changePace.attr('dataPace'));
			changePace.attr('dataoff',true);
		}
		if(!$('.fatherSpan').hasClass('active')){
			$('.childenSpan').removeClass('active');
			$('.childenSpan').parent().children('.pay-num').children('span').attr('dataoff',false);
			$('.childenSpan').parent().children('.pay-num').children('span').text(0);
		}
		
		
		commercial.text(toThousands(
			toKilobit(thirdInsurance.text())+
			toKilobit(damageInsurance.text())+
			toKilobit(stolenInsurance.text())+
			toKilobit(glassInsurance.text())+
			toKilobit(combustInsurance.text())+
			toKilobit(noDeductibleInsurance.text())+
			toKilobit(noLiabilityInsurance.text())+
			toKilobit(passengerInsurance.text())+
			toKilobit(carBodyInsurance.text())
		));
		sumPrice();
		refreshChange();
	})


// 总价
	function sumPrice(){
		sum_price.text(toThousands(
			toKilobit($('#carReferencePrice').text())+
			toKilobit(interest.text())+
			toKilobit(fundamental.text())+
			toKilobit(commercial.text())
		))
	}
//获取最新数据
	function refreshChange(){
		carCostParam.carPrice = toKilobit(carReferencePrice.text()); //指导价格
		carCostParam.loanYears = loanYears.attr('datavalue');              //还款周期
		carCostParam.displacement = usageTax.attr('datavalue');  
		carCostParam.seatCount = trafficInsurance.attr('datavalue');
		carCostParam.thirdInsureClaim = parseInt(thirdInsurance.attr('datavalue'));
		carCostParam.prepaymentPercent = parseFloat(payFunction.attr('datarate'));
		carCostParam.carBodyInsureClaim = parseFloat(carBodyInsurance.attr('datavalue'));
		carCostParam.rateInterest = parseFloat(referenceInterest.text()); //利率
		carCostParam.CommInsureCheck.thirdCheck = changebool(thirdInsurance.attr('dataoff'));
		carCostParam.CommInsureCheck.damageCheck =changebool(damageInsurance.attr('dataoff'));
		carCostParam.CommInsureCheck.stolenCheck =changebool(stolenInsurance.attr('dataoff'));
		carCostParam.CommInsureCheck.glassCheck =changebool(glassInsurance.attr('dataoff'));
		carCostParam.CommInsureCheck.combustCheck =changebool(combustInsurance.attr('dataoff'));
		carCostParam.CommInsureCheck.noDeductibleCheck =changebool(noDeductibleInsurance.attr('dataoff'));
		carCostParam.CommInsureCheck.noLiabilityCheck =changebool(noLiabilityInsurance.attr('dataoff'));
		carCostParam.CommInsureCheck.passengerCheck =changebool(passengerInsurance.attr('dataoff'));
		carCostParam.CommInsureCheck.carBodyCheck =changebool(carBodyInsurance.attr('dataoff'));
		carCounter(carCostParam);
	}
//页面数据更新
	function carCounter(jsonObj) {
		var carResult = newCarCost.getCarPurchaseCost(jsonObj);
		
		monthPay.text(toThousands(carResult.carLoanFee.monthPay)); //月供
		interest.text(toThousands(carResult.getLoanMoreCost()));                           //利息								                       //基本费用
		purchaseTax.text(toThousands(carResult.carPurchaseTax.purchaseTax)); //购置税
		consumption.text(toThousands(carResult.carPurchaseTax.excise));   //消费税  
		//		registration = $('#registration'),                      //上牌费用
		usageTax.text(toThousands(carResult.carPurchaseTax.usageTax)); //车船税
		trafficInsurance.text(toThousands(carResult.carInsurance.trafficInsurance)); //交强险
		commercial.text(toThousands(carResult.getCommerceInsurance()));                          // 商业保险
		thirdInsurance.text(toThousands(carResult.carInsurance.thirdInsurance)); // 第三者责任险
		damageInsurance.text(toThousands(carResult.carInsurance.damageInsurance)); // 车辆损失险
		stolenInsurance.text(toThousands(carResult.carInsurance.stolenInsurance)); // 全车盗抢险
		glassInsurance.text(toThousands(carResult.carInsurance.glassInsurance));// 玻璃单独破碎险
		combustInsurance.text(toThousands(carResult.carInsurance.combustInsurance));              // 自燃损失险
		noDeductibleInsurance.text(toThousands(carResult.carInsurance.noDeductibleInsurance)); // 不计免赔特约险
		noLiabilityInsurance.text(toThousands(carResult.carInsurance.noLiabilityInsurance)); // 无过责任险
		passengerInsurance.text(toThousands(carResult.carInsurance.passengerInsurance));          // 车上人员责任险
		carBodyInsurance.text(toThousands(carResult.carInsurance.carBodyInsurance));      // 车身划痕险\
//基本费用
		fundamental.text(toThousands(
			toKilobit(purchaseTax.text())+
			toKilobit(consumption.text())+
			toKilobit(registration.text())+
			toKilobit(usageTax.text())+
			toKilobit(trafficInsurance.text())
		));
		prepayment.text(toThousands(carResult.carLoanFee.prepayment+carResult.getCommerceInsurance()+toKilobit(fundamental.text()))); //首付
		sumPrice();
	}
//
	function changebool(str){
		if(str==="true"){
			return true;
		}else if(str==="false"){	
			return false;
		}
	}
//取得千位点数数字
	function toKilobit(str) {
		return parseInt(str.replace(/[^0-9]+/g, ''));
	}
//改变千位点数数字	
	function toThousands(num) {
		return(num || 0).toString().replace(/(\d)(?=(?:\d{3})+$)/g, '$1,');
	}
})

