SELECT
	t1.id,
	t1.deliveryId,
	t1.orderId,
	t1.logisticianId,
	t1.warehouseId,
	t1.channelOrderId,
	t1.channelId,
	t1.channelName,
	t1.channelCustomerId,
	t1.orderedOn,
	t1.locale,
	t1.currencyCode,
	t1.shippingProvider,
	t1.shippingService,
	t1.attributes,
	t1.createdOn,
	t1.destinationType,
	t1.phone,
	t1.addressee,
	t1.addresseeAddOn,
	t1.street,
	t1.streetAddOn,
	t1.postalCode,
	t1.city,
	t1.countryCode,
	t1.warehouseName,
	SUM(t1.quantityOrdered) AS quantityOrdered,
	IF(t1.fulfillmentId IS NOT NULL, IF(SUM(t1.quantityOrdered) - IFNULL(SUM(t1.quantityShipped), 0) < 0, 0, SUM(t1.quantityOrdered) - IFNULL(SUM(t1.quantityShipped), 0)), 0) AS quantityCancelled,
	IFNULL(SUM(t1.quantityShipped), 0) AS quantityShipped,
	IFNULL(SUM(t1.quantityReturned), 0) AS quantityReturned,
	IFNULL(SUM(t1.quantityPreparedCancellation), 0) AS quantityPreparedCancellation,
	IFNULL(SUM(t1.quantityPrepared), 0) AS quantityPrepared,
	IFNULL(SUM(t1.quantityPacked), 0) AS quantityPacked,
	IFNULL(SUM(t1.quantityPreparedReturn), 0) AS quantityPreparedReturn
FROM
	(SELECT
		 d_o.id,
		 d_o.deliveryId,
		 d_o.orderId,
		 d_o.logisticianId,
		 d_o.warehouseId,
		 d_o.channelOrderId,
		 d_o.channelId,
		 d_o.channelName,
		 d_o.channelCustomerId,
		 d_o.orderedOn,
		 d_o.locale,
		 d_o.currencyCode,
		 d_o.shippingProvider,
		 d_o.shippingService,
		 d_o.attributes,
		 d_o.createdOn,
		 dd.type destinationType,
		 dd.phone,
		 dd.addressee,
		 dd.addresseeAddOn,
		 dd.street,
		 dd.streetAddOn,
		 dd.postalCode,
		 dd.city,
		 dd.countryCode,
		 f.id fulfillmentId,
		 w.name warehouseName,
		 dli.quantity quantityOrdered,
		 shipped.quantity quantityShipped,
		 returned.quantity quantityReturned,
		 prepared_cancelled.quantity quantityPreparedCancellation,
		 prepared.quantity quantityPrepared,
		 packed.quantity quantityPacked,
		 prepared_return.quantity quantityPreparedReturn
	 FROM
		 delivery_orders d_o
			 JOIN delivery_destinations dd ON dd.deliveryOrderId = d_o.id
			 LEFT JOIN warehouses w ON w.logisticianId = d_o.logisticianId AND w.id = d_o.warehouseId
			 LEFT JOIN fulfillments f ON d_o.id = f.deliveryOrderId
			 JOIN delivery_line_items filterable_dli ON filterable_dli.deliveryOrderId = d_o.id
			 JOIN delivery_line_items dli ON dli.deliveryOrderId = d_o.id
			 LEFT JOIN prepared_oost prepared_cancelled ON prepared_cancelled.deliveryOrderId = d_o.id AND dli.lineItemId = prepared_cancelled.lineItemId
			 LEFT JOIN prepared_line_items prepared ON prepared.deliveryOrderId = d_o.id AND dli.lineItemId = prepared.lineItemId
			 LEFT JOIN prepared_return_line_items prepared_return ON prepared_return.deliveryOrderId = d_o.id AND dli.lineItemId = prepared_return.lineItemId
			 LEFT JOIN packed_parcels ppi ON ppi.deliveryOrderId = d_o.id
			 LEFT JOIN packed_line_items packed ON packed.parcelId = ppi.id AND dli.lineItemId = packed.lineItemId
			 LEFT JOIN shipped_parcels spi ON spi.fulfillmentId = f.id
			 LEFT JOIN shipped_line_items shipped ON shipped.parcelId = spi.id AND dli.lineItemId = shipped.lineItemId
			 LEFT JOIN returns ri ON ri.deliveryOrderId = d_o.id
			 LEFT JOIN returned_line_items returned ON returned.returnId = ri.id AND dli.lineItemId = returned.lineItemId
	 WHERE d_o.logisticianId = :logisticianId AND d_o.warehouseId = :warehouseId AND (dd.city IN (:city_1, :city_2, :city_3) OR dd.street LIKE :street)
	 GROUP BY d_o.id, dli.lineItemId) t1
GROUP BY
	t1.id
ORDER BY
	t1.createdOn, t1.logisticianId DESC

